<template>
    <div>
        <standardization/>
    </div>
</template>

<script>
import Authenticated from "../Layouts/Authenticated"
import standardization from "../Components/standardisation/standardization"

export default {
    layout: Authenticated,
    components: {standardization},
    data() {
        return {
            loading: true
        }
    }
};
</script>

<style>
</style>
