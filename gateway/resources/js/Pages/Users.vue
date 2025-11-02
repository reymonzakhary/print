<template>
    <div>
        <Users :users="users"/>
    </div>
</template>

<script>
import Authenticated from "../Layouts/Authenticated"
import Users from "../Components/users/Users.vue";

export default {
    name: 'users-page',
    layout: Authenticated,
    components: {Users},
    props: {
        users: Object,
    },
    data() {
        return {
            loading: true
        }
    }
};
</script>

<style>
</style>
