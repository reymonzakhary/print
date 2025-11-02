const state = () => ({
   // category
   tenants: [],
   activeTenant: {},
   newTenant: {
      name: '',
      password: '',
      supplier: false,
      apiurl: '',
      apifiles: [],
      logo: {}
   },
   tenantComponent: 'TenantsTable'
})

const mutations = {
   // category
   populate_tenants(state, tenants) {
      state.tenants = tenants
   },
   set_active_tenant(state, tenant) {
      state.activeTenant = tenant
   },
   set_tenant_component(state, component) {
      state.activeTenant = component
   },
   update_new_tenant(state, {
      key,
      value
   }) {
      state.newTenant[key] = value
   },
}

const actions = {
   obtain_tenants({
      commit,
      state
   }, { per_page, page }) {
      axios.get(`/clients?per_page=${(per_page) ? per_page : 10}&page=${(page) ? page : 1}`)
         .then(response => {
            commit('populate_tenants', response.data.data)
            commit('pagination/populate_pagination', response.data, { root: true })
            // console.log(state.clients);
         })
   }
}

const getters = {
   suppliers: (state) => {
      let newusers = []
      for (let i = 0; i < state.tenants.length; i++) {
         const user = state.tenants[i];

         if (user.supplier_id !== null) {
            newusers.push(user)
         }
      }
      return newusers
   },
   resellers: (state) => {
      let newusers = []
      for (let i = 0; i < state.tenants.length; i++) {
         const user = state.tenants[i];

         if (user.supplier_id === null) {
            newusers.push(user)
         }
      }
      return newusers
   },
}

export default {
   namespaced: true,
   state,
   mutations,
   actions,
   getters,
}
