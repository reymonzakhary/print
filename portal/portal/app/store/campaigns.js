export default {
  state: () => ({
    // new campaign
    new_campaign_name: "",
    new_campaign_files: [],
    new_campaign_config: {
      schedule: null,
      export_html: true,
      export_image: false,
      export_pdf: false,
      multiple: true,
      output_availability: 0,
      output: "partner_first",
    },
    // Campaigns overview
    campaigns: [],
    // selected campaign
    selected_campaign: {},
    selected_campaign_templates: [],
    selected_campaign_selected_template: [],
    // exports
    selected_export: {},
    // generated
    campaigngenerating: false,
    campaign_output: [],
    filter: "",
    modal_name: "",
  }),
  mutations: {
    // new campaign
    set_new_campaign_name(state, name) {
      state.new_campaign_name = name;
    },
    set_new_campaign_files(state, files) {
      state.new_campaign_files = files;
    },
    set_new_campaign_config(state, data) {
      state.new_campaign_config[data.label] = data.data;
    },
    clear_new_campaign(state) {
      (state.new_campaign_name = ""), (state.new_campaign_files = []);
    },
    // campaigns overview
    store_campaigns(state, campaigns) {
      state.campaigns = campaigns;
    },
    add_campaign(state, campaign) {
      state.campaigns.push(campaign);
    },
    update_campaign(state, campaign) {
      let index = state.campaigns.findIndex((x) => x.id === campaign.id);
      state.campaigns[index] = campaign;
    },
    remove_campaign(state, id) {
      let index = state.campaigns.findIndex((x) => x.id === id);
      state.campaigns.splice(index, 1);
    },
    // selected campaign
    set_selected_campaign(state, campaign) {
      state.selected_campaign = campaign;
    },
    set_selected_campaign_templates(state, templates) {
      state.selected_campaign_templates = templates;
    },
    set_selected_campaign_config(state, config) {
      state.selected_campaign.config = config;
    },
    // exports
    set_selected_campaign_exports(state, exports) {
      if (!state.selected_campaign.exports) {
        state.selected_campaign.exports = exports;
      } else {
        state.selected_campaign.exports.unshift(exports);
      }
    },
    delete_selected_campaign_exports(state, exports) {
      let index = state.selected_campaign.exports.findIndex((x) => x.id === exports.id);
      state.selected_campaign.exports.splice(index, 1);
    },
    set_selected_campaign_template_assets(state, { id, path }) {
      let index = state.selected_campaign_templates.findIndex((x) => x.id === id);
      state.selected_campaign_templates[index].assets = path;
    },
    remove_selected_campaign_template(state, id) {
      let index = state.selected_campaign_templates.findIndex((x) => x.id === id);
      state.selected_campaign_templates.splice(index, 1);
    },
    set_selected_campaign_selected_template(state, template) {
      state.selected_campaign_selected_template = template;
    },
    // selected campaign
    set_selected_export(state, entry) {
      state.selected_export = entry;
    },
    update_filter(state, filter) {
      state.filter = filter;
    },
    set_modal_name(state, name) {
      state.modal_name = name;
    },
    // generated
    set_campaign_generating(state, generating) {
      state.campaigngenerating = generating;
    },
    set_campaign_output(state, files) {
      state.campaign_output = files;
    },
  },
  getters: {
    campaign_exists: (state) => state.campaigns.some((el) => el.id === id),
  },
  actions: {
    async get_campaigns({ commit, dispatch }) {
      await this.$axios
        .get("modules/campaigns?per_page=99999")
        .then((response) => {
          commit("store_campaigns", response.data.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_single_campaign({ commit, dispatch }, id) {
      await this.$axios
        .get(`modules/campaigns/${id}`)
        .then((response) => {
          commit("set_selected_campaign", response.data.data);
          dispatch("get_single_campaign_templates", id);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_single_campaign_templates({ commit, dispatch }, id) {
      await this.$axios
        .get(`modules/campaigns/${id}/templates`)
        .then((resp) => {
          commit("set_selected_campaign_templates", resp.data.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
