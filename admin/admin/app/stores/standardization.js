import { defineStore } from "pinia";

export const useStandardizationStore = defineStore("standardization", {
  state: () => ({

  }),

  actions: {
    // general actions
    setModalComponent(component) {
      this.modalComponent = component;
    },

    setPerPage(amount) {
      this.per_page = amount;
      localStorage.setItem("per_page", amount);
    },

    toggleView(view) {
      this.view = view;
      localStorage.setItem("view", view);
    },

    togglePageLoading() {
      this.pageLoading = !this.pageLoading;
    },

    initialiseStore() {
      if (localStorage.getItem("view")) {
        this.view = localStorage.getItem("view");
      }
      if (localStorage.getItem("per_page")) {
        this.per_page = localStorage.getItem("per_page");
      }
    },

    // category mutations converted to actions
    setActiveCategory(cat) {
      this.activeCategory = cat;
    },

    setEditData(data) {
      this.editData = data;
    },

    setCategoryEdit(bool) {
      this.categoryEdit = bool;
    },

    setCategoryUnmatched(bool) {
      this.categoryUnmatched = bool;
    },

    // boxes mutations converted to actions
    setActiveBox(box) {
      this.activeBox = box;
    },

    setBoxEdit(bool) {
      this.boxEdit = bool;
    },

    setBoxUnmatched(bool) {
      this.boxUnmatched = bool;
    },

    // options mutations converted to actions
    setActiveOption(option) {
      this.activeOption = option;
    },

    setOptionEdit(bool) {
      this.optionEdit = bool;
    },

    setOptionUnmatched(bool) {
      this.optionUnmatched = bool;
    },
  },
});
