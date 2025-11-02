<template>
    <div>
        <Clients/>
    </div>
</template>

<script>
import Authenticated from "../Layouts/Authenticated"
import Clients from "../Components/clients/Clients";

export default {
    layout: Authenticated,
    components: {Clients},
    data() {
        return {
            loading: true

        }
    }
};
</script>

<style>
</style>
