const state = () => ({
    // category
    categories: [],
    unmatchedCats: [],
    activeCategory: {},
    categoryEdit: false,
    categoryUnmatched: false,

    // boxes
    boxes: [],
    unmatchedBoxes: [],
    activeBox: {},
    boxEdit: false,
    boxUnmatched: false,

    // options
    options: [],
    unmatchedOptions: [],
    activeOption: {},
    optionEdit: false,
    optionUnmatched: false,

    // for detaching or editing
    editData: {},
    modalComponent: "",

    // relational or list all
    view: 'relational',
    pageLoading: false,
    per_page: 20
})

const mutations = {
    // category
    populate_categories(state, categories) {
        state.categories = categories
    },
    populate_unmatchedCats(state, unmatched) {
        state.unmatchedCats = unmatched
    },
    set_active_category(state, cat) {
        state.activeCategory = cat
    },
    set_edit_data(state, data) {
        state.editData = data
    },
    set_category_edit(state, bool) {
        state.categoryEdit = bool
    },
    set_category_unmatched(state, bool) {
        state.categoryUnmatched = bool
    },

    // boxes
    populate_boxes(state, boxes) {
        state.boxes = boxes
    },
    populate_unmatchedBoxes(state, unmatched) {
        state.unmatchedBoxes = unmatched
    },
    set_active_box(state, box) {
        state.activeBox = box
    },
    set_box_edit(state, bool) {
        state.boxEdit = bool
    },
    set_box_unmatched(state, bool) {
        state.boxUnmatched = bool
    },

    // options
    populate_options(state, options) {
        state.options = options
    },
    populate_unmatchedOptions(state, unmatched) {
        state.unmatchedOptions = unmatched
    },
    set_active_option(state, option) {
        state.activeOption = option
    },
    set_option_edit(state, bool) {
        state.optionEdit = bool
    },
    set_option_unmatched(state, bool) {
        state.optionUnmatched = bool
    },

    // general
    set_modal_component(state, component) {
        state.modalComponent = component
    },
    set_per_page(state, amount) {
        state.per_page = amount
        localStorage.setItem('per_page', amount);
    },
    toggle_view(state, view) {
        state.view = view
        localStorage.setItem('view', view);
    },
    toggle_page_loading(state) {
        state.pageLoading = !state.pageLoading
    },
    initialise_store(state) {
        // Check if the ID exists
        if (localStorage.getItem('view')) {
            state.view = localStorage.getItem('view')
        }
        if (localStorage.getItem('per_page')) {
            state.per_page = localStorage.getItem('per_page')
        }
    }
}

const actions = {
    // paginated
    async obtain_categories({commit}, {per_page, page, filter}) {
        await axios.get(`/categories?per_page=${(per_page) ? per_page : 20}&page=${(page) ? page : 1}&filter=${(filter) ? filter : ''}`).then(response => {
            commit('populate_categories', response.data)
        })
    },
    async obtain_unmatched_categories({commit}) {
        await axios.get('/unmatched/categories').then(response => {
            commit('populate_unmatchedCats', response.data)
        })
    },
    // paginated
    async obtain_boxes({commit}, {per_page, page, filter}) {
        await axios.get(`/boxes?per_page=${(per_page) ? per_page : 20}&page=${(page) ? page : 1}&filter=${(filter) ? filter : ''}`).then(response => {
            commit('populate_boxes', response.data)
        })
    },
    async obtain_unmatched_boxes({commit}) {
        await axios.get(`/unmatched/boxes`).then(response => {
            commit('populate_unmatchedBoxes', response.data)
        })
    },
    // paginated
    async obtain_options({commit}, {per_page, page, filter}) {
        await axios.get(`/options?per_page=${(per_page) ? per_page : 20}&page=${(page) ? page : 1}&filter=${(filter) ? filter : ''}`).then(response => {
            commit('populate_options', response.data)
        })
    },
    async obtain_unmatched_options({commit}) {
        await axios.get(`/unmatched/options`).then(response => {
            commit('populate_unmatchedOptions', response.data)
        })
    },
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
}
