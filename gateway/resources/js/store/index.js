export default {
   state: {
      active_theme: 'light',
      auto_theme: Boolean,
      active_menu_item: 'home'
   },

   mutations: {
      set_active_theme(state, theme) {
         state.active_theme = theme
      },
      set_auto_theme(state, boolean) {
         state.auto_theme = boolean
      },
      set_active_menu_item(state, value) {
         state.active_menu_item = value
      }
   }
}