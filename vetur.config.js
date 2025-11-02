// vetur.config.js
/** @type {import('vls').VeturConfig} */
module.exports = {
   // **optional** default: `{}`
   // override vscode settings
   // Notice: It only affects the settings used by Vetur.
   settings: {
     "vetur.useWorkspaceDependencies": true,
     "vetur.experimental.templateInterpolationService": true
   },
   // **optional** default: `[{ root: './' }]`
   // support monorepos
   projects: [
     './gateway', // shorthand for only root.
     {
       root: './gateway',
     },
     './portal', // shorthand for only root.
     {
       root: './portal',
     }
   ]
 }