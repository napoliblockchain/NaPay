module.exports = {
  "globDirectory": "html/",
  "globPatterns": [
    "**/*.{css,map,js,eot,svg,ttf,woff,woff2,png,jpg,txt,md,flow,mjs,hash,mp3,ico,less,json,html}"
  ],
  "swSrc": "html/sw-base.js",
  "swDest": "html/sw.js",
  "globIgnores": [
    "../workbox-cli-config.js",
    "assets/**"
  ]
};
