// BASE
require('./bootstrap');
import Vue from 'vue'
import {createInertiaApp} from '@inertiajs/inertia-vue'


// VUEX
import Vuex from 'vuex'
import tenants from "./store/modules/tenants"
import users from "./store/modules/users"
import standardization from "./store/modules/standardization"
import notification from "./store/modules/notification"
import pagination from "./store/modules/pagination"

// FONT_AWESOME
import {FontAwesomeIcon, FontAwesomeLayers, FontAwesomeLayersText} from '@fortawesome/vue-fontawesome'
import {library} from '@fortawesome/fontawesome-svg-core'
import {fal} from '@fortawesome/pro-light-svg-icons'
import {fad} from '@fortawesome/pro-duotone-svg-icons'

// COMPONENTS
import vSelect from 'vue-select'
// custom scrollbar
import Vuebar from 'vuebar';

// DIRECTIVES
import VTooltip from 'v-tooltip'

// initiate Inertia & Vue
createInertiaApp({
    resolve: name => {
        const page = require(`./Pages/${name}`).default
        page.layout = page.layout || Layout
        return page
    },
    setup({el, App, props, plugin}) {
        // BASE
        Vue.use(plugin)

        // VUEX
        Vue.use(Vuex)
        const store = new Vuex.Store({
            modules: {
                pagination,
                tenants,
                standardization,
                notification,
                users,
            },
        });

        // FONT-AWESOME
        library.add(fal)
        library.add(fad)

        Vue.component('font-awesome-icon', FontAwesomeIcon)
        Vue.component('font-awesome-layers', FontAwesomeLayers)
        Vue.component('font-awesome-layers-text', FontAwesomeLayersText)

        // COMPONENTS
        Vue.use(vSelect);
        // Auto require all components globally to avoid registering locally
        const files = require.context('./', true, /\.vue$/i)
        files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
        Vue.use(Vuebar);

        // DIRECTIVES
        Vue.use(VTooltip, {
            defaultClass: 'bg-black text-white p-2 z-50 text-xs rounded shadow m-1 border border-gray-900'
        })

        new Vue({
            store,
            render: h => h(App, props),
        }).$mount(el)
    },
})
