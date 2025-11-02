<template>
    <div>
        <div
            class="fixed top-0 left-0 z-50 flex items-center justify-center w-screen h-screen"
        >
            <div
                class="p-10 text-lg font-bold tracking-wide text-center uppercase bg-white rounded shadow-lg"
            >
                <div class="flex justify-center pb-4 text-blue-500">
                    <font-awesome-icon :icon="['fad', 'person-dolly']" swap-opacity size="7x"/>
                </div>
                Loading all Boxes &amp; Options
                <font-awesome-icon
                    :icon="['fad', 'spinner-third']"
                    spin
                    class="ml-4 text-blue-500"
                />
            </div>
        </div>

        <div
            class="fixed top-0 bottom-0 left-0 right-0 z-40 opacity-75 bg-gradient-to-br from-green-400 via-blue-500 to-indigo-500"
        ></div>
    </div>
</template>

<script>
export default {
    name: "PageLoader"
};
</script>

<style>
</style>
