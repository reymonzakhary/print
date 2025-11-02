<template>
    <div>
        <transition name="fade">
            <component :is="component" :items="users"></component>
        </transition>
    </div>
</template>

<script>
import UsersTable from "./UsersTable.vue";
import NewUser from "./new_user/NewUser.vue";
// import { mapState } from "vuex";

export default {
    components: {UsersTable, NewUser},
    props: {
        users: Object
    },
    data() {
        return {
            component: 'UsersTable',
        };
    },
};
</script>

<style>
</style>
