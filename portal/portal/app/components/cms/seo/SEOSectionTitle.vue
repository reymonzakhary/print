<template>
    <h3 class="py-1 mb-1 text-xs font-bold tracking-wide text-gray-500 capitalize">
        <font-awesome-icon :icon="icon" />
        {{ title }}
    </h3>
</template>

<script>
export default {
    name: 'SEOSectionTitle',
    props: {
        title: {
            type: String,
            required: true
        },
        icon: {
            type: Array,
            required: true
        }
    }
}
</script>