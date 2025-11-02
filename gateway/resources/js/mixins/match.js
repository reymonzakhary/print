import Axios from "axios"

var match = {
    methods: {
        /**
         * @description
         * @date 2021-01-06
         * @param {*} itemtype String
         * @param {*} pr_slug String
         * @param {*} tenant_id
         * @param {*} type
         * @param {*} sup_slug
         * @param  category
         * @param  box
         */
        attach(itemtype, pr_slug, tenant_id, type, sup_slug, category, box) {
            let url = "";
            switch (itemtype) {
                case "category":
                    url = `categories/${pr_slug}/attach`;
                    break;
                case "box":
                    url = `boxes/${pr_slug}/attach`;
                    break;
                case "option":
                    url = `options/${pr_slug}/attach`;
                    break;

                default:
                    break;
            }

            Axios.post(url, {
                type: type,
                tenant_id: tenant_id,
                slug: sup_slug
            })

                .then(response => {
                    this.set_notification({
                        text: response.data.message,
                        status: 'green',
                    })
                    this.$store.dispatch('standardization/obtain_categories')
                    this.close()
                })
                .catch(err => {
                    this.set_notification({
                        text: err.response.data.message,
                        status: 'red',
                    })

                    for (const error in err.response.data.errors) {
                        if (Object.hasOwnProperty.call(err.response.data.errors, error)) {
                            const message = err.response.data.errors[error];
                            this.set_notification({
                                text: message,
                                status: 'red',
                            })
                        }
                    }
                })
        },
    }
}

export default match;
