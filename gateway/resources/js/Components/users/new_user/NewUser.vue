<template>
    <div class="p-2">
        <header class="p-2 font-bold uppercase tracking wide">
            <button
                class="mr-4 text-blue-500"
                @click="$parent.component = 'UsersTable'"
            >
                <font-awesome-icon :icon="['fal', 'chevron-left']"/>
                Back
            </button>
            <font-awesome-icon :icon="['fal', 'user']"/>
            Add User
        </header>

        <div class="flex justify-center">
            <div
                class="h-full p-4 overflow-hidden overflow-y-auto bg-white divide-y rounded shadow lg:w-2/3 dark:divide-gray-900 dark:bg-gray-800"
                style="max-height: calc(100vh - 7.5rem)"
            >
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

export default {
    components: {GeneralInfo},
    computed: {
        ...mapState({
            newTenant: (state) => state.tenants.newTenant,
        }),
    },
    data() {
        return {
            newItem: {},
            error: "",
            files: false,
        };
    },
    methods: {
        closeModal() {
            this.$parent.closeModal();
        },
    },
};
</script>

<style>
</style>
