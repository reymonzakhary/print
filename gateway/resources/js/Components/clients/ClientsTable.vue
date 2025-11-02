<template>
    <div class="p-4">
        <transition name="fade">
            <component
                :is="component"
                :item="activeClient"
                :type="type"
            ></component>
        </transition>

        <button class="mb-2 text-blue-500" @click="$parent.component = 'NewClient'">
            <font-awesome-icon :icon="['fal', 'plus']"/>
            <font-awesome-icon :icon="['fal', 'user']"/>
            New client
        </button>

        <div
            class="p-4 overflow-hidden bg-white rounded shadow"
            style="max-height: calc(100vh - 5rem)"
        >
            <!-- flexbox table -->
            <header class="sticky top-0 flex font-bold blur">
                <div class="flex-1">Id</div>
                <div class="flex-1">Name</div>
                <div class="flex-1">Is supplier</div>
                <div class="flex-1">Publish products?</div>
                <div class="flex-1">Categories</div>
                <div class="flex-1">Config</div>
            </header>

            <div
                class="flex flex-wrap items-center rounded cursor-pointer hover:bg-gray-100"
                :class="{
					'bg-blue-100 text-blue-500': activeClient.id === client.id,
				}"
                v-for="(client, i) in filtered_clients"
                :key="`client_${i}`"
                @click="set_active_client(client)"
            >
                <div class="flex-1 capitalize">
                    {{ client.id }}
                </div>
                <div class="flex-1 capitalize">
                    {{ client.name }}
                </div>
                <div class="flex-1 capitalize">
                    {{ client.supplier_id ? "yes" : "no" }}
                </div>
                <div class="flex-1 capitalize">
                    {{ client.share_products }}
                </div>
                <div class="flex-1 capitalize">
                    {{ client.shared_categories.length }}
                    <font-awesome-icon
                        :icon="['fal', 'info']"
                        @click="show_categories = client.id"
                    />
                </div>
                <div
                    class="flex w-full p-2 bg-blue-100 rounded"
                    v-if="show_categories === client.id"
                >
                    <button @click="show_categories = false">close</button>
                    <div
                        v-for="(category, index) in client.shared_categories"
                        :key="`client_category_${index}`"
                    >
                        {{ category.name }}
                    </div>
                </div>
                <div class="flex-1 capitalize">
                    <button
                        class="text-blue-500 "
                    >
                        <font-awesome-icon :icon="['fal', 'cloud-download-alt']"/>
                        Import assortment
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {mapActions, mapMutations, mapState} from "vuex";

export default {
    data() {
        return {
            filter: "",
            show_categories: false,
            menuItems: [
                {
                    action: "edit",
                    icon: "pencil",
                    title: "Edit",
                    classes: "text-blue-900",
                    show: true,
                },

                {
                    action: "delete",
                    icon: "trash",
                    title: "Delete",
                    classes: "text-red-500",
                    show: true,
                },
            ],
            type: "producer",
            component: "",
        };
    },
    computed: {
        ...mapState({
            clients: (state) => state.clients.clients,
            activeClient: (state) => state.clients.activeClient,
        }),
        filtered_clients() {
            if (this.filter.length > 0) {
                return this.clients.filter((client) => {
                    return Object.values(client).some((val) => {
                        if (val !== null) {
                            return val
                                .toString()
                                .toLowerCase()
                                .includes(this.filter.toLowerCase());
                        }
                    });
                });
            }
            return this.clients;
        },
    },
    mounted() {
        this.obtain_clients();
    },
    methods: {
        ...mapMutations({
            set_active_client: "clients/set_active_client",
        }),
        ...mapActions({
            obtain_clients: "clients/obtain_clients",
        }),
        closeModal() {
            this.component = "";
        },
        /**
         * This processes the menu clicks
         * @param {String} event menu-item clicked
         * @return {Function}  respective funtion gets executed
         * @todo create functions for the current console.log's
         */
        menuItemClicked(event, client) {
            switch (event) {
                case "edit":
                    this.$store.commit("clients/set_category_edit", true);
                    break;
                case "delete":
                    this.component = "DeleteItem";
                    break;
                default:
                    break;
            }
        },
    },
};
</script>

<style>
</style>
