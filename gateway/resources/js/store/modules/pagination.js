const state = () => ({
   pagination: {
      current_page: 1,
      first_page_url: "http://manager.prindustry.test/clients?page=1",
      from: 1,
      last_page: 1,
      last_page_url: "http://manager.prindustry.test/clients?page=1",
      links: [{
         "url": null,
         "label": "&laquo; Previous",
         "active": false
      },
      {
         "url": "http:\/\/manager.prindustry.test\/clients?page=1",
         "label": "1",
         "active": true
      },
      {
         "url": null,
         "label": "Next &raquo;",
         "active": false
      }],
      next_page_url: null,
      path: "http://manager.prindustry.test/clients",
      per_page: 10,
      prev_page_url: null,
      to: 1,
      total: 1,
   },
})


const mutations = {
   populate_pagination(state, pagination) {
      console.log(pagination)
      state.pagination.current_page = pagination.current_page
      state.pagination.first_page_url = pagination.first_page_url
      state.pagination.from = pagination.from
      state.pagination.last_page = pagination.last_page
      state.pagination.last_page_url = pagination.last_page_url
      state.pagination.links = pagination.links
      state.pagination.next_page_url = pagination.next_page_url
      state.pagination.path = pagination.path
      state.pagination.per_page = pagination.per_page
      state.pagination.prev_page_url = pagination.prev_page_url
      state.pagination.to = pagination.to
      state.pagination.total = pagination.total
   }
}

export default {
   namespaced: true,
   state,
   mutations,
}
