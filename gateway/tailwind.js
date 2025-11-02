module.exports = {
   content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
   ],
   darkMode: 'class', // or 'media' or 'class'
   theme: {
      container: {
         center: true,
      },
      fontFamily: {
         sans: [
            'Noto Sans',
            'system-ui',
            '-apple-system',
            'BlinkMacSystemFont',
            '"Segoe UI"',
            'Roboto',
            '"Helvetica Neue"',
            'Arial',
            '"Noto Sans"',
            'sans-serif',
            '"Apple Color Emoji"',
            '"Segoe UI Emoji"',
            '"Segoe UI Symbol"',
            '"Noto Color Emoji"',
         ],
         serif: ['Georgia', 'Cambria', '"Times New Roman"', 'Times', 'serif'],
         mono: ['Menlo', 'Monaco', 'Consolas', '"Liberation Mono"', '"Courier New"', 'monospace'],
      },
      boxShadow: {
         xs: '0 0 0 1px rgba(0, 0, 0, 0.05)',
         sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
         DEFAULT: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
         md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
         lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
         xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
         '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
         '3xl': '0 35px 60px -15px rgba(0, 0, 0, 0.3)',
         inner: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
         outline: '0 0 0 3px rgba(66, 153, 225, 0.5)',
         focus: '0 0 0 3px rgba(66, 153, 225, 0.5)',
         none: 'none',
      },
      extend: {
         colors: {
            blue: {
               100: '#ccd7ff',
               200: '#99afff',
               300: '#6688ff',
               400: '#3360ff',
               500: '#0038ff',
               600: '#002dcc',
               700: '#002299',
               800: '#001666',
               900: '#000b33',
            },
            cyan: {
               100: '#e9f5f9',
               200: '#bce2ed',
               300: '#8fcee1',
               400: '#8fcee1',
               500: '#3095b4',
               600: '#2a829d',
               700: '#002dcc',
               800: '#123843',
               900: '#061216',
            },
         }
      },
   },
   variants: {
      extend: {
         visibility: ['responsive', 'hover', 'focus', 'group-hover'],
         backgroundColor: ['responsive', 'hover', 'focus', 'active', 'group-hover'],
         borderColor: ['responsive', 'hover', 'focus'],
         textColor: ['responsive', 'hover', 'focus'],
         borderRadius: ['responsive', 'first', 'last'],
         borderWidth: ['responsive', 'first', 'last'],
      }
   },
}
