import axios from "axios"
import {mapMutations} from "vuex";

var selectParent = {
    methods: {
        ...mapMutations({
            set_active_category: "standardization/set_active_category",
            set_active_box: "standardization/set_active_box",
            set_active_option: "standardization/set_active_option",
            populate_categories: "standardization/populate_categories",
            populate_boxes: "standardization/populate_boxes",
            populate_options: "standardization/populate_options",
        }),
        selectCategory(category, slug) {
            this.set_active_category(category);

            if (this.view === "relational") {
                this.set_active_box({});
                this.set_active_option({});
                this.populate_boxes([]);
                this.populate_options([]);
                this.loading = slug;

                axios
                    .get(`/categories/${slug}/boxes`)
                    .then(response => {
                        this.populate_boxes(response.data);
                        this.loading = null;
                    })
                    .catch(err => {
                        console.log(err.response);
                    });
            }
        },
        selectBox(box, slug) {
            this.set_active_box(box);

            if (this.view === "relational") {
                this.populate_options([]);
                this.set_active_option({});
                this.loading = slug;

                axios
                    .get(
                        `/categories/${this.activeCategory.slug}/boxes/${slug}/options`
                    )
                    .then(response => {
                        this.populate_options(response.data);
                        this.loading = null;
                    })
                    .catch(err => {
                        console.log(err.response);
                    });
            }
            if (this.view === "boxes & options") {
                this.populate_options([]);
                this.set_active_option({});
                this.loading = slug;

                axios
                    .get(`/boxes/${slug}/options`)
                    .then(response => {
                        this.populate_options(response.data);
                        this.loading = null;
                    })
                    .catch(err => {
                        console.log(err.response);
                    });
            }
        },
    }
}

export default selectParent;
