<template>
    <div class="w-full py-4 mt-4 border-t">
        <div class="text-xs font-bold tracking-wide text-gray-500 uppercase">
            Attach to standard {{ type }}
        </div>
        <!-- {{items}} -->
        <div class="flex items-center">
            <!-- <v-select
                :options="items.data"
                label="name"
                class="w-full bg-white rounded-l"
                v-model="selected[i]"
            >
            </v-select> -->
            <div class="relative flex">
                <input
                    class="w-full px-2 py-1 mx-2 bg-white border border-blue-300 rounded shadow-md dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    type="text"
                    :placeholder="`Search all ${type}`"
                    v-model="filter"
                />
                <font-awesome-icon
                    :icon="['fal', 'filter']"
                    class="absolute right-0 mt-2 mr-4 text-gray-600"
                />

                <div
                    v-if="searchitems.length > 0"
                    class="absolute w-full -mt-1 overflow-y-auto bg-white border rounded-b shadow-md top-9 max-h-96"
                >
                    <ul class="divide-y">
                        <li
                            v-for="(item, i) in searchitems"
                            :key="'searchitem_' + i"
                            class="block p-4 transition-colors cursor-pointer hover:bg-gray-100 hover:text-blue-500"
                            @click="
								(selected[i] = item), (filter = ''), (searchitems = [])
							"
                        >
                            {{ item.name }}
                        </li>
                    </ul>
                </div>
            </div>
            <div v-if="selected.length > 0">{{ selected[i].name }}</div>
            <button
                class="w-24 px-2 py-1 ml-2 font-bold text-blue-500 border border-blue-500 rounded hover:bg-blue-100"
                @click="
					attach(
						type,
						selected[i].slug,
						match.tenant_id,
						matchType,
						match.slug ? match.slug : match.object.slug,
						category ? category.slug : null,
						box ? box.slug : null
					)
				"
            >
                Attach
            </button>
        </div>
    </div>
</template>

<script>
import match from "../../../mixins/match";

export default {
    mixins: [match],
    props: {
        items: Array | Object,
        match: Object,
        category: Object,
        box: Object,
        i: Number,
        type: String,
        matchType: String
    },
    data() {
        return {
            selected: [],
            filter: "",
            searchitems: []
        };
    },
    watch: {
        filter: _.debounce(function (val) {
            if (val.length > 0) {
                switch (this.type) {
                    case "category":
                        axios
                            .get(`/categories?filter=${val}&per_page=99999999999999`)
                            .then(response => {
                                this.searchitems = response.data.data;
                            });
                        break;

                    case "box":
                        axios
                            .get(`/boxes?filter=${val}&per_page=99999999999999`)
                            .then(response => {
                                this.searchitems = response.data.data;
                            });
                        break;

                    case "option":
                        axios
                            .get(`/options?filter=${val}&per_page=99999999999999`)
                            .then(response => {
                                this.searchitems = response.data.data;
                            });
                        break;

                    default:
                        break;
                }
            }
        }, 300)
    },
    methods: {
        close() {
            this.$store.commit("standardization/set_category_edit", false);
            this.$parent.close();
        }
    }
};
</script>

<style>
</style>
