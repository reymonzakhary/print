import { defineStore } from "pinia";
import { useAPI } from "#imports";

export const useBoxesAndOptions = defineStore("Boops", {
  state: () => ({
    options: [],
    boxes: [],
  }),
  actions: {
    async getBoxes() {
      const res = await useAPI().get(`/boxes?per_page=99999999999999`);
      this.boxes = res.data;
      return res.data;
    },
    async getOptions() {
      const res = await useAPI().get(`/options?per_page=99999999999999`);
      this.options = res.data;
      return res.data;
    },
  },
});
