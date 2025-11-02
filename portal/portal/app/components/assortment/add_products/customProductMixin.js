import { mapMutations } from "vuex";
const images = import.meta.glob("assets/images/assortments_portal/en/*.svg", {
  eager: true,
});

export default {
  methods: {
    ...mapMutations({
      set_component: "product_wizard/set_wizard_component",
    }),
    getImgUrl(categories) {
      const categoriesInArray = categories.split(" ");
      let imgUrl = "";

      categoriesInArray.forEach((category) => {
        const src = category.toLowerCase();
        if (images[`/assets/images/assortments_portal/en/${src}.svg`]) {
          imgUrl =
            images[`/assets/images/assortments_portal/en/${src}.svg`].default;
        }
      });

      return imgUrl;
    },
  },
};
