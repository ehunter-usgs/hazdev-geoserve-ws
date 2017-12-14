'use strict';

var config = require('./config');


var connect = {
  options: {
    hostname: '*'
  },

  rules: [
    {
      from: '^(' + config.ini.MOUNT_PATH + ')?/(index|places|regions|layers)\\.(json)\\??(.*)$',
      to: '/$2.php?format=$3&$4'
    },
    {
      from: '^' + config.ini.MOUNT_PATH + '/(.*)$',
      to: '/$1'
    }
  ],

  proxies: [
    {
      context: '/theme/',
      host: 'localhost',
      port: config.templatePort,
      rewrite: {
        '^/theme': ''
      }
    }
  ],

  dev: {
    options: {
      base: [config.build + '/' + config.src + '/htdocs'],
      port: config.srcPort,
      livereload: config.liveReloadPort,
      open: 'http://127.0.0.1:' + config.srcPort + config.ini.MOUNT_PATH + '/',
      middleware: function (connect, options, middlewares) {
        middlewares.unshift(
          require('grunt-connect-rewrite/lib/utils').rewriteRequest,
          require('grunt-connect-proxy/lib/utils').proxyRequest,
          require('gateway')(options.base[0], {
            '.php': 'php-cgi',
            'env': {
              'PHPRC': 'node_modules/hazdev-template/dist/conf/php.ini'
            }
          })
        );
        return middlewares;
      }
    }
  },

  dist: {
    options: {
      base: [config.dist + '/htdocs'],
      port: config.distPort,
      keepalive: true,
      open: 'http://127.0.0.1:' + config.distPort + config.ini.MOUNT_PATH + '/',
      middleware: function (connect, options, middlewares) {
        middlewares.unshift(
          require('grunt-connect-rewrite/lib/utils').rewriteRequest,
          require('grunt-connect-proxy/lib/utils').proxyRequest,
          require('gateway')(options.base[0], {
            '.php': 'php-cgi',
            'env': {
              'PHPRC': 'node_modules/hazdev-template/dist/conf/php.ini'
            }
          })
        );
        return middlewares;
      }
    }
  },
  
  template: {
    options: {
      base: ['node_modules/hazdev-template/dist/htdocs'],
      port: config.templatePort
    }
  }
};


module.exports = connect;
