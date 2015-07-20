<?php

class RegionsFactory extends GeoserveFactory {

  protected static $SUPPORTED_TYPES = array(
    'admin',
    'authoritative',
    'fe',
    'neic_catalog',
    'neic_response'
  );

  /**
   * Get regions containing point.
   *
   * @param $query {RegionsQuery}
   *        query object
   * @return object of regions keyed by type
   * @throws Exception
   */
  public function get ($query) {
    $data = array();

    if (in_array('admin', $query->type)) {
      $data['admin'] = $this->getAdmin($query);
    }
    if (in_array('authoritative', $query->type)) {
      $data['authoritative'] = $this->getAuthoritative($query);
    }
    if (in_array('fe', $query->type)) {
      $data['fe'] = $this->getFE($query);
    }
    if (in_array('neic_catalog', $query->type)) {
      $data['neic_catalog'] = $this->getNEICCatalog($query);
    }
    if (in_array('neic_response', $query->type)) {
      $data['neic_response'] = $this->getNEICResponse($query);
    }

    return $data;
  }

  /**
   * @return {Array}
   *         An array of supported types
   */
  public function getSupportedTypes () {
    return RegionsFactory::$SUPPORTED_TYPES;
  }

  /**
   * Get Admin Regions
   *
   * @param $query {RegionsQuery}
   *        query object
   */
  public function getAdmin ($query) {
    //Checks for latitude and longitude
    if ($query->latitude === null || $query->longitude === null) {
      throw new Exception('"latitude", and "longitude" are required');
    }

    // create sql
    $sql = 'WITH search AS (SELECT' .
        ' ST_SetSRID(ST_MakePoint(:longitude,:latitude),4326)::geography' .
        ' AS point' .
        ')';
    // bound parameters
    $params = array(
      ':latitude' => $query->latitude,
      ':longitude' => $query->longitude
    );

    $sql .= ' SELECT' .
        ' iso as iso' .
        ', country as country' .
        ', region as region' .
        ', id';

    if ($query->includeGeometry) {
      $sql .= ', ST_AsGeoJSON(shape) as shape';
    }

    $sql .= ' FROM search, admin' .
        ' WHERE search.point && shape' .
        ' AND ST_Intersects(search.point, shape)' .
        ' ORDER BY ST_Area(shape) ASC';

    return $this->execute($sql, $params);
  }

  /**
  * Get Authoritative Regions
  *
  * @param $query {RegionsQuery}
  * query object
  */
  public function getAuthoritative ($query) {
    // Checks for latitude and longitude
    if ($query->latitude === null || $query->longitude === null) {
      throw new Exception('"latitude", and "longitude" are required');
    }

    // create sql
    $sql = 'WITH search AS (SELECT' .
      ' ST_SetSRID(ST_MakePoint(:longitude,:latitude),4326)::geography' .
      ' AS point' .
      ')';
    //bound parameters
    $params = array(
      ':latitude' => $query->latitude,
      ':longitude' => $query->longitude
    );

    $sql .= ' SELECT' .
        ' name as name' .
        ', network as network' .
        ', id';

    if ($query->includeGeometry) {
      $sql .= ', ST_AsGeoJSON(shape) as shape';
    }

    $sql .= ' FROM search, authoritative' .
        ' WHERE search.point && shape' .
        ' AND ST_Intersects(search.point, shape)' .
        ' ORDER BY priority ASC';

    return $this->execute($sql, $params);
  }

  /**
   * Get FE Regions
   *
   * @param $query {RegionsQuery}
   *        query object
   */
  public function getFE ($query) {
    // Checks for latitude and longitude
    if ($query->latitude === null || $query->longitude === null) {
      throw new Exception('"latitude", and "longitude" are required');
    }

    // create sql
    $sql = 'WITH search AS (SELECT' .
        ' ST_SetSRID(ST_MakePoint(:longitude,:latitude),4326)::geography' .
        ' AS point' .
        ')';
    // bound parameters
    $params = array(
        ':latitude' => $query->latitude,
        ':longitude' => $query->longitude);

    $sql .= ' SELECT' .
        ' num as number' .
        ', place as name' .
        ', id';

    if ($query->includeGeometry) {
      $sql .= ', ST_AsGeoJSON(shape) as shape';
    }

    $sql .= ' FROM search, fe_view' .
        ' WHERE search.point && shape' .
        ' AND ST_Intersects(search.point, shape)' .
        ' ORDER BY priority ASC, ST_Area(shape) ASC';

    return $this->execute($sql, $params);
  }

  /**
   * Get NEIC Regions
   *
   * @param $query {RegionsQuery}
   *        query object
   * @param $NEIC {String}
   *        'neic_catalog' or 'neic_response'
   */
  public function getNEIC ($query, $NEIC) {
    //Checks for latitude and longitude
    if ($query->latitude === null || $query->longitude === null) {
      throw new Exception('"latitude", and "longitude" are required');
    }

    // create sql
    $sql = 'WITH search AS (SELECT' .
        ' ST_SetSRID(ST_MakePoint(:longitude,:latitude),4326)::geography' .
        ' AS point' .
        ')';
    // bound parameters
    $params = array(
      ':latitude' => $query->latitude,
      ':longitude' => $query->longitude
    );

    $sql .= ' SELECT' .
        ' name as name' .
        ', magnitude as magnitude' .
        ', id';

    if ($query->includeGeometry) {
      $sql .= ', ST_AsGeoJSON(shape) as shape';
    }

    $sql .= ' FROM search, ' . $NEIC .
        ' WHERE search.point && shape' .
        ' AND ST_Intersects(search.point, shape)' .
        ' ORDER BY ST_Area(shape) ASC';

    return $this->execute($sql, $params);
  }

  /**
   * Get NEIC catalog Regions
   * calls getNEIC
   *
   * @param $query {RegionsQuery}
   *        query object
   */
  public function getNEICCatalog ($query) {
    return $this->getNEIC($query, 'neic_catalog');
  }

  /**
   * Get NEIC Response Regions
   * calls getNEIC
   *
   * @param $query {RegionsQuery}
   *        query object
   */
  public function getNEICResponse ($query) {
    return $this->getNEIC($query, 'neic_response');
  }

}
