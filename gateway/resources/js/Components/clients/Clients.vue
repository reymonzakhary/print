<template>
    <div>
        <transition name="fade">
            <component :is="component"></component>
        </transition>
    </div>
</template>

<script>
import TenantsTable from "./TenantsTable.vue";
import NewTenant from "./new_tenant/NewTenant.vue";
// import { mapState } from "vuex";

export default {
    components: {TenantsTable, NewTenant},
    data() {
        return {
            component: "TenantsTable",
        };
    },
    // computed: {
    //     ...mapState({
    //         component: state => state.clients.clientComponent
    //     })
    // }
};
</script>

<style>
</style>
