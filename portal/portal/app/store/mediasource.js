export default {
  state: () => ({
    mediaSources: [],
    selectedMediaSource: {},
    selectedMSRules: [],
    MSDisk: "",
    MSPath: "",
    MSAccess: null,
    MSComponent: "NewMediaSourceName",
  }),
  mutations: {
    setMSComponent(state, component) {
      state.MSComponent = component;
    },
    setMediaSources(state, mediasources) {
      state.mediaSources = mediasources;
    },
    setMediaSourceRules(state, rules) {
      state.mediaSourceRules = rules;
    },
    addMediaSourceRules(state, rule) {
      state.selectedMediaSource.rules.push(rule);
    },
    removeMediaSourceRule(state, id) {
      let i = state.selectedMediaSource.rules.findIndex((rule) => rule.id === id);
      state.selectedMediaSource.rules.splice(i, 1);
    },
    addMediaSource(state, ms) {
      state.mediaSources.push(ms);
    },
    deleteMediaSource(state, id) {
      let i = state.mediaSources.findIndex((ms) => ms.id === id);
      state.mediaSources.splice(i, 1);
    },
    setSelectedMS(state, ms) {
      state.selectedMediaSource = ms;
    },
  },
  actions: {
    async get_media_sources({ commit }) {
      const api = useAPI();
      await api.get("media-sources").then((response) => {
        commit("setMediaSources", response.data);
      });
    },
  },
};
