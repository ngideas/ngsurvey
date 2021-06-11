(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined'
    ? factory(exports, require('chart.js'))
    : typeof define === 'function' && define.amd
    ? define(['exports', 'chart.js'], factory)
    : ((global = global || self), factory((global.ChartESMFacade = {}), global.Chart));
})(this, function (exports, ChartNS) {
  'use strict';

  ChartNS = ChartNS && Object.prototype.hasOwnProperty.call(ChartNS, 'default') ? ChartNS['default'] : ChartNS;

  // copy all
  Object.assign(exports, ChartNS);
  // create internal structure
  exports.default = ChartNS;
  exports.Chart = ChartNS;
  // Animations
  exports.Animations = ChartNS.animationService;
  // platforms
  Object.assign(exports, ChartNS.platforms);
  [
    ['controllers', 'Controller'],
    ['scales', 'Scale'],
    ['elements', ''],
    ['plugins', ''],
  ].forEach((setup) => {
    const reg = ChartNS.registry[setup[0]];
    const suffix = setup[1];
    Object.keys(reg.items).forEach((id) => {
      const instance = reg.get(id);
      exports[id[0].toUpperCase() + id.slice(1) + suffix] = instance;
    });
  });

  // flat helpers
  Object.keys(ChartNS.helpers).forEach((helper) => {
    const v = ChartNS.helpers[helper];
    // core and extra helpers are inlined
    if (typeof v === 'function' && helper[0] !== '_') {
      exports[helper] = v;
    } else {
      Object.keys(v)
        .filter((d) => d[0] !== '_')
        .forEach((hi) => {
          exports[hi] = v[hi];
        });
    }
  });
});
