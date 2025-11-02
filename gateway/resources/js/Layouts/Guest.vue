<template>
    <div id="app"
         class="flex items-center justify-center h-screen text-blue-900 bg-gradient-to-br from-green-400 via-blue-500 to-indigo-500">
        <div class="fixed top-0 flex w-screen text-white">

            <nav class="flex items-center justify-between w-screen h-12 px-2 md:w-screen">
                <div class="flex items-center">

                    <img src="images/Prindustry_logo_wit.png" alt="Prindustry Logo" id="prindustry-logo"
                         class="h-8 mr-1 max-w-none"/>
                    <span class="mt-2 text-sm uppercase">Manager</span>
                </div>
            </nav>
        </div>
        <slot/>
    </div>
</template>

<script>
export default {
    name: "Guest"
}
</script>
