'use strict';

var View = require('mvc/View'),

    Util = require('util/Util');


// Default values to be used by constructor
var _DEFAULTS = {
  header: null,
  noDataMessage: 'Nearby city data not available.'
};


/**
 * Class: NearbyCitiesView
 *
 * @param params {Object}
 *      Configuration options. See _DEFAULTS for more details.
 */
var NearbyCitiesView = function (params) {
  var _this,
      _initialize,

      _header,
      _noDataMessage;


  // Inherit from parent class
  _this = View(params||{});

  /**
   * @constructor
   *
   */
  _initialize = function (params) {
    params = Util.extend({}, _DEFAULTS, params);

    _header = params.header;
    _noDataMessage = params.noDataMessage;

    _this.render();
  };

  /**
   * Convert azimuth in degree's into compass points.
   */
  _this._compasswinds = function(azimuth) {
    var fullwind = 22.5,
        directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N'];

    return directions[(Math.round((azimuth%360)/fullwind))];
  };

  /**
   * Destroy all the things.
   */
  _this.destroy = Util.compose(function () {
    _header = null;
    _noDataMessage = null;

    _initialize = null;
    _this = null;
  }, _this.destroy);

  /**
   * Convert Kilometers to miles
   */
  _this._kmToMi = function (km) {
    return (km * 0.621371);
  };

  /**
   * Put commas into a number for display.
   */
  _this._numberWithCommas = function (x) {
    var parts = x.toString().split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
  };

  /**
   * Update map to display current location.
   */
  _this.render = function () {
    var markup,
        nearbycitiesresponse,
        properties,
        i,
        feature;

    markup = [(_header !== null) ? _header : ''];

    try {
      nearbycitiesresponse = _this.model.get('places').event;

      markup.push('<ol class="nearbyCities no-style">');
      for (i = 0; i < nearbycitiesresponse.count; i++) {
        feature = nearbycitiesresponse.features[i];
        properties = feature.properties;
        markup.push(
          '<li>' +
            '<span class="citydistance">' +
              properties.distance +
              'km (' +
              Math.round(this._kmToMi(properties.distance)) + 'mi) ' +
            '</span>' +
            '<span class="direction">' +
              _this._compasswinds(properties.azimuth) + ' of ' +
            '</span>' +
            '<span class="cityname">' +
              properties.name +
            '</span>' +
            '<span class="admin1name">' +
              ', ' + properties.admin1_name +
            '</span>' +
            '<span class="countryname">' +
              ', ' + properties.country_name +
            '</span>' +
            '<span class="population">' +
              ' population ' +_this._numberWithCommas(properties.population) +
            '</span>' +
          '</li>'
        );
      }
      markup.push('</ol>');
    } catch (e) {
      markup.push('<p class="alert info">' + _noDataMessage + '</p>');
    }

    _this.el.innerHTML = markup.join('');
  };

  // Always call the constructor
  _initialize(params);
  params = null;
  return _this;
};


module.exports = NearbyCitiesView;
