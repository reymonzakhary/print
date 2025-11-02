<template>
    <div>
        <Contracts/>
    </div>
</template>

<script>
import Authenticated from "../Layouts/Authenticated";
import Contracts from "../Components/contracts/Contracts.vue";

export default {
    layout: Authenticated,
    components: {Contracts},
    data() {
        return {
            loading: true,
        };
    },
};
</script>

<style>
</style>
