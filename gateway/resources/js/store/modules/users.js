const state = () => ({
    // category
    users: [],
    // activeTenant: {},
    // newTenant: {
    //     name: '',
    //     password: '',
    //     supplier: false,
    //     apiurl: '',
    //     apifiles: [],
    //     logo: {}
    // },
    // tenantComponent: 'TenantsTable'
})

const mutations = {
    // category
    populate_users(state, users) {
        state.users = users
    },
    // set_active_tenant(state, tenant) {
    //     state.activeTenant = tenant
    // },
    // set_tenant_component(state, component) {
    //     state.activeTenant = component
    // },
    // update_new_tenant(state, { key, value }) {
    //     state.newTenant[key] = value
    // }
}

const actions = {
    obtain_users({commit, state}) {
        axios.get('/get-users').then(response => {
            commit('populate_users', response.data.data)
            console.log(state.users);
        })
    }
}

const getters = {
    // suppliers: (state) => {
    //     let newusers = []
    //     for (let i = 0; i < state.tenants.length; i++) {
    //         const user = state.tenants[i];

    //         if (user.supplier_id !== null) {
    //             newusers.push(user)
    //         }
    //     }
    //     return newusers
    // },
    // resellers: (state) => {
    //     let newusers = []
    //     for (let i = 0; i < state.tenants.length; i++) {
    //         const user = state.tenants[i];

    //         if (user.supplier_id === null) {
    //             newusers.push(user)
    //         }
    //     }
    //     return newusers
    // },
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
}
