export default {
  state: () => ({
    // ACL
    acl: null,
    // App version
    version: "2.4.14",
    // axios headers
    headers: {},
    // axios default URL
    baseUrl: null,
    /**
     * File manager windows configuration
     * 1 - only one file manager window
     * 2 - one file manager window with directories tree module
     * 3 - two file manager windows
     */
    windowsConfig: 2,
    // App language
    lang: null,
    // Translations (/src/lang)
    // translations: {
    //    ru: Object.freeze(ru),
    //    en: Object.freeze(en),
    //    ar: Object.freeze(ar),
    //    sr: Object.freeze(sr),
    //    cs: Object.freeze(cs),
    //    de: Object.freeze(de),
    //    es: Object.freeze(es),
    //    nl: Object.freeze(nl),
    //    'zh-CN': Object.freeze(zh_CN),
    //    fa: Object.freeze(fa),
    //    it: Object.freeze(it),
    //    tr: Object.freeze(tr),
    //    fr: Object.freeze(fr),
    //    'pt-BR': Object.freeze(pt_BR),
    // },

    // show or hide hidden files
    hiddenFiles: false,
    // Context menu items
    contextMenu: [
      [
        {
          name: "open",
          icon: "folder-open",
        },
        {
          name: "audioPlay",
          icon: "play",
        },
        {
          name: "videoPlay",
          icon: "play",
        },
        {
          name: "view",
          icon: "eye",
        },
        {
          name: "editImage",
          icon: "image",
        },
        {
          name: "edit",
          icon: "file-signature",
        },
        {
          name: "select",
          icon: "check",
        },
        {
          name: "download",
          icon: "download",
        },
      ],
      [
        {
          name: "copy",
          icon: "copy",
        },
        {
          name: "cut",
          icon: "cut",
        },
        {
          name: "rename",
          icon: "edit",
        },
        {
          name: "paste",
          icon: "clipboard",
        },
        {
          name: "zip",
          icon: "file-zipper",
        },
        {
          name: "unzip",
          icon: "file-zipper",
        },
      ],
      [
        {
          name: "delete",
          icon: "trash-can",
          class: "text-red-600",
        },
      ],
      [
        {
          name: "properties",
          icon: "rectangle-list",
        },
      ],
    ],
    // Image extensions for view and preview
    imageExtensions: ["png", "jpg", "jpeg", "gif", "webp", "bmp", "ico", "svg", "tif", "tiff"],
    // Image extensions for cropping
    cropExtensions: ["png", "jpg", "jpeg"],
    // audio extensions for play
    audioExtensions: ["ogg", "mp3", "aac", "wav"],
    // video extensions for play
    videoExtensions: ["webm", "mp4"],
    // File extensions for code editor
    textExtensions: {
      sh: "text/x-sh",
      // styles
      css: "text/css",
      less: "text/x-less",
      sass: "text/x-sass",
      scss: "text/x-scss",
      html: "text/html",
      htm: "text/html",
      // js
      js: "text/javascript",
      ts: "text/typescript",
      vue: "text/x-vue",
      // text
      htaccess: "text/plain",
      env: "text/plain",
      txt: "text/plain",
      text: "text/plain",
      log: "text/plain",
      ini: "text/x-ini",
      xml: "application/xml",
      md: "text/x-markdown",
      // c-like
      java: "text/x-java",
      c: "text/x-csrc",
      cpp: "text/x-c++src",
      cs: "text/x-csharp",
      scl: "text/x-scala",
      php: "php",
      // DB
      sql: "text/x-sql",
      // other
      pl: "text/x-perl",
      py: "text/x-python",
      lua: "text/x-lua",
      swift: "text/x-swift",
      rb: "text/x-ruby",
      go: "text/x-go",
      yaml: "text/x-yaml",
      json: "application/json",
    },
  }),
  mutations: {
    /**
     * Initialize App settings from server
     * @param state
     * @param data
     */
    initSettings(state, data) {
      if (!state.lang) state.lang = data.lang;
      if (!state.windowsConfig) state.windowsConfig = data.windowsConfig;
      state.acl = data.acl;
      state.hiddenFiles = data.hiddenFiles;
    },
    /**
     * Set Hide or Show hidden files
     * @param state
     */
    toggleHiddenFiles(state) {
      state.hiddenFiles = !state.hiddenFiles;
    },
  },
  getters: {
    baseUrl(state) {
      return state.baseUrl;
    },
    /**
     * Headers
     * @param state
     * @return {*}
     */
    headers(state) {
      return state.headers;
    },
  },
};
