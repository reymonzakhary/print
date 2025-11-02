export default {
  methods: {
    /**
     * Bytes to KB, MB, ..
     * @param bytes
     * @returns {string}
     */
    bytesToHuman(bytes) {
      const sizes = ["Bytes", "KB", "MB", "GB", "TB"];

      if (bytes === 0) return "0 Bytes";

      const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);

      if (i === 0) return `${bytes} ${sizes[i]}`;

      return `${(bytes / 1024 ** i).toFixed(1)} ${sizes[i]}`;
    },

    /**
     * Timestamp to date
     * @param timestamp
     * @returns {string}
     */
    timestampToDate(timestamp) {
      // if date not defined
      if (timestamp === undefined || !this.$store.state.fm.settings.lang) return "-";

      const date = new Date(timestamp * 1000);

      return date.toLocaleString(this.$store.state.fm.settings.lang);
    },

    /**
     * Mime type to icon
     * @param mime
     * @returns {*}
     */
    mimeToIcon(mime) {
      // mime types
      const mimeTypes = {
        // image
        "image/gif": "file-image",
        "image/png": "file-image",
        "image/jpeg": "file-image",
        "image/bmp": "file-image",
        "image/webp": "file-image",
        "image/tiff": "file-image",
        "image/svg+xml": "file-image",

        // text
        "text/plain": "file-lines",

        // code
        "text/javascript": "file-code",
        "application/json": "file-code",
        "text/markdown": "file-code",
        "text/html": "file-code",
        "text/css": "file-code",

        // audio
        "audio/midi": "file-audio",
        "audio/mpeg": "file-audio",
        "audio/webm": "file-audio",
        "audio/ogg": "file-audio",
        "audio/wav": "file-audio",
        "audio/aac": "file-audio",
        "audio/x-wav": "file-audio",
        "audio/mp4": "file-audio",

        // video
        "video/webm": "file-video",
        "video/ogg": "file-video",
        "video/mpeg": "file-video",
        "video/3gpp": "file-video",
        "video/x-flv": "file-video",
        "video/mp4": "file-video",
        "video/quicktime": "file-video",
        "video/x-msvideo": "file-video",
        "video/vnd.dlna.mpeg-tts": "file-video",

        // archive
        "application/x-bzip": "file-zipper",
        "application/x-bzip2": "file-zipper",
        "application/x-tar": "file-zipper",
        "application/gzip": "file-zipper",
        "application/zip": "file-zipper",
        "application/x-7z-compressed": "file-zipper",
        "application/x-rar-compressed": "file-zipper",

        // application
        "application/pdf": "file-pdf",
        "application/rtf": "file-word",
        "application/msword": "file-word",

        "application/vnd.ms-word": "file-word",
        "application/vnd.ms-excel": "file-excel",
        "application/vnd.ms-powerpoint": "file-powerpoint",

        "application/vnd.oasis.opendocument.text": "file-word",
        "application/vnd.oasis.opendocument.spreadsheet": "file-excel",
        "application/vnd.oasis.opendocument.presentation": "file-powerpoint",

        "application/vnd.openxmlformats-officedocument.wordprocessingml": "file-word",
        "application/vnd.openxmlformats-officedocument.spreadsheetml": "file-excel",
        "application/vnd.openxmlformats-officedocument.presentationml": "file-powerpoint",
      };

      if (mimeTypes[mime] !== undefined) {
        return mimeTypes[mime];
      }

      // file blank
      return "file";
    },

    /**
     * File extension to icon (font awesome)
     * @returns {*}
     * @param extension
     */
    extensionToIcon(extension) {
      // files extensions
      const extensionTypes = {
        // images
        gif: "file-image",
        png: "file-image",
        jpeg: "file-image",
        jpg: "file-image",
        bmp: "file-image",
        psd: "file-image",
        svg: "file-image",
        ico: "file-image",
        ai: "file-image",
        tif: "file-image",
        tiff: "file-image",
        webp: "file-image",

        // text
        txt: "file-lines",
        json: "file-lines",
        log: "file-lines",
        ini: "file-lines",
        xml: "file-lines",
        md: "file-lines",
        env: "file-lines",

        // code
        js: "file-code",
        php: "file-code",
        css: "file-code",
        cpp: "file-code",
        class: "file-code",
        h: "file-code",
        java: "file-code",
        sh: "file-code",
        swift: "file-code",
        htm: "file-code",
        html: "file-code",

        // audio
        aif: "file-audio",
        cda: "file-audio",
        mid: "file-audio",
        mp3: "file-audio",
        mpa: "file-audio",
        ogg: "file-audio",
        wav: "file-audio",
        wma: "file-audio",

        // video
        wmv: "file-video",
        avi: "file-video",
        mpeg: "file-video",
        mpg: "file-video",
        flv: "file-video",
        mp4: "file-video",
        mkv: "file-video",
        mov: "file-video",
        ts: "file-video",
        "3gpp": "file-video",

        // archive
        zip: "file-zipper",
        arj: "file-zipper",
        deb: "file-zipper",
        pkg: "file-zipper",
        rar: "file-zipper",
        rpm: "file-zipper",
        "7z": "file-zipper",
        "tar.gz": "file-zipper",

        // application
        pdf: "file-pdf",

        rtf: "file-word",
        doc: "file-word",
        docx: "file-word",
        odt: "file-word",

        xlr: "file-excel",
        xls: "file-excel",
        xlsx: "file-excel",

        ppt: "file-powerpoint",
        pptx: "file-powerpoint",
        pptm: "file-powerpoint",
        xps: "file-powerpoint",
        potx: "file-powerpoint",
      };

      if (extension && extensionTypes[extension.toLowerCase()] !== undefined) {
        return extensionTypes[extension.toLowerCase()];
      }

      // blank file
      return "file";
    },
  },
};
