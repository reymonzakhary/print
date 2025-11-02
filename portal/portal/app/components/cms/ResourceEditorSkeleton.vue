<template>
    <div class="grid grid-cols-[14fr_3fr] gap-4">

        <div>
            <div class="h-10 mb-4 bg-gray-200 rounded dark:bg-gray-900 loading"></div>

            <div class="grid grid-cols-[2fr_2fr_3fr] gap-4">
                <div class="bg-gray-200 dark:bg-gray-900 h-36 loading"></div>
                <div class="bg-gray-200 dark:bg-gray-900 loading"></div>
                <div class="bg-gray-200 dark:bg-gray-900 loading"></div>
            </div>

            <div class="grid grid-cols-[3fr_1fr] gap-4 mt-4">
                <div class="bg-gray-200 dark:bg-gray-900 loading h-36"></div>
                <div class="bg-gray-200 dark:bg-gray-900 loading"></div>
            </div>
        </div>

        <div class="bg-gray-200 dark:bg-gray-900 loading"></div>
    </div>
</template>

<script>
export default {
    name: 'ResourceEditorSkeleton',
}
</script>