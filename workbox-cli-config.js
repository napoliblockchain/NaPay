module.exports = {
  "globDirectory": "web/",
  "globPatterns": [
    "**/*.{css,map,js,eot,svg,ttf,woff,woff2,png,jpg,txt,md,flow,mjs,hash,mp3,ico,less,json,html}"
  ],
  "swSrc": "web/sw-base.js",
  "swDest": "web/sw.js",
  "globIgnores": [
    "../workbox-cli-config.js",
    "assets/**"
  ]
};
