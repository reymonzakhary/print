<template>
    <div class="w-full py-4 border-t">
        <div class="text-xs font-bold tracking-wide text-gray-500 uppercase">
            Add {{ match.tenant_name }}'s
        </div>
        <span class="font-bold">{{
                match.name ? match.name : match.object.name
            }}</span>
        as
        <div class="flex items-center justify-between">
            <input
                type="text"
                v-model="new_name"
                class="w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
            />
            <button
                class="w-24 px-2 py-1 ml-2 font-bold text-blue-500 border border-blue-500 rounded hover:bg-blue-100"
                @click="add()"
            >
                Add
            </button>
        </div>
    </div>
</template>

<script>
import match from "../../../mixins/match";

export default {
    mixins: [match],
    props: {
        items: Array,
        match: Object,
        category: Object,
        box: Object,
        i: Number,
        type: String,
        matchType: String,
    },
    data() {
        return {
            new_name: this.match.name ? this.match.name : this.match.object.name,
        };
    },
    methods: {
        add() {
            let url = "";
            switch (this.type) {
                case "category":
                    url = `categories`;
                    break;

                case "box":
                    url = `/boxes`;
                    break;

                case "option":
                    url = `/options`;
                    break;

                default:
                    break;
            }
            axios
                .post(url, {
                    name: this.new_name,
                    iso: "EN",
                })
                .then((response) => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green",
                    });
                    this.attach(
                        this.type,
                        response.data.data.slug,
                        this.match.tenant_id,
                        this.matchType,
                        this.match.slug
                    );
                })
                .catch((err) => {
                    this.set_notification({
                        text: err.response.data.message,
                        status: "red",
                    });
                });
        },
    },
};
</script>

<style>
</style>
