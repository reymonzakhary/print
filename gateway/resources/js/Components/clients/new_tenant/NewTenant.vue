<template>
    <div class="p-2">
        <header class="p-2 font-bold uppercase tracking wide">
            <button class="mr-4 text-blue-500" @click="$parent.component = 'TenantsTable'">
                <font-awesome-icon :icon="['fal', 'chevron-left']"/>
                Back
            </button>
            <font-awesome-icon :icon="['fal', 'user-crown']"/>
            Add Tenant
        </header>

        <div class="flex justify-center">
            <div class="w-1/3 p-4 m-2 bg-white rounded shadow dark:bg-gray-800">
                <GeneralInfo></GeneralInfo>
            </div>
            <!-- <div class="w-1/3 p-4 m-2 bg-white rounded shadow">
                <SupplierInfo :newClient="newTenant"></SupplierInfo>
            </div> -->
        </div>

        <!-- <footer class="flex justify-center">
            <button
                class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
                @click="add()"
            >
                <font-awesome-icon :icon="['fal', 'plus']" />
                Add {{ type }}
            </button>
        </footer> -->
    </div>
</template>

<script>
import {mapState} from "vuex";
import GeneralInfo from "./GeneralInfo.vue";
import SupplierInfo from "./SupplierInfo.vue";

export default {
    components: {GeneralInfo, SupplierInfo},
    props: {
        type: String,
        category: Object,
        box: Object,
        item: Object
    },
    computed: {
        ...mapState({
            newTenant: state => state.tenants.newTenant
        })
    },
    data() {
        return {
            newItem: {},
            error: "",
            files: false
        };
    },
    methods: {
        add() {
            let url = "";
            switch (this.type) {
                case "category":
                    url = `category`;
                    break;

                case "box":
                    url = `categories/${this.category.slug}/boxes`;
                    break;

                case "option":
                    url = `categories/${this.category.slug}/boxes/${this.box.slug}/options`;
                    break;

                default:
                    break;
            }
            this.axiosPost(url);
        },
        axiosPost(url) {
            axios
                .post(url, this.newItem)
                .then(response => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green"
                    });
                    this.$store.dispatch("standardization/obtain_categories");
                    this.closeModal();
                })
                .catch(err => {
                    this.error = err.response.message;
                });
        },
        closeModal() {
            this.$parent.closeModal();
        }
    }
};
</script>

<style>
</style>
