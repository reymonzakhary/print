<template>
	<div class="p-4">
		<header class="p-2 font-bold uppercase tracking wide">
			<font-awesome-icon :icon="['fal', 'users']" />
			Users
			<button
				class="ml-2 text-sm font-normal text-blue-500 transition hover:underline"
				@click="$parent.component = 'NewUser'"
			>
				<font-awesome-icon :icon="['fal', 'plus']" />
				<font-awesome-icon :icon="['fal', 'user']" />
				Add User
			</button>
		</header>
		<div
			class="p-4 overflow-hidden bg-white rounded shadow dark:divide-gray-900 dark:bg-gray-800"
			style="max-height: calc(100vh - 7rem)"
			v-if="items"
		>
			<!-- flexbox table -->
			<header
				class="sticky top-0 flex text-sm font-bold tracking-wide text-gray-500 uppercase backdrop-blur"
			>
				<!-- User data -->
				<div class="flex-grow-0 px-2 py-1 pr-6">user</div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1 border-r"></div>

				<!-- Company details -->
				<div class="flex-1 px-2 py-1">company</div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1"></div>
				<div class="flex-1 px-2 py-1"></div>
			</header>
			<header
				class="sticky top-0 flex text-xs font-bold tracking-wide text-gray-500 uppercase border-b backdrop-blur"
			>
				<!-- User data -->
				<div class="flex-grow-0 px-2 py-1 pr-4">Id</div>
				<div class="flex-1 px-2 py-1">Email</div>
				<div class="flex-1 px-2 py-1">Username</div>
				<div class="flex-1 px-2 py-1 border-r">Verified</div>

				<!-- Company details -->
				<div class="flex-1 px-2 py-1 bg-gray-50">name</div>
				<div class="flex-1 px-2 py-1 bg-gray-50">description</div>
				<div class="flex-1 px-2 py-1 bg-gray-50">url</div>
				<div class="flex-1 px-2 py-1 bg-gray-50">coc</div>
				<div class="flex-1 px-2 py-1 bg-gray-50">tax_nr</div>

				<!-- <div class="flex-1 px-2 py-1">street</div>
				<div class="flex-1 px-2 py-1">nr</div>
				<div class="flex-1 px-2 py-1">zip</div>
				<div class="flex-1 px-2 py-1">city</div> -->
			</header>

			<div
				class="flex flex-wrap items-center cursor-pointer group hover:bg-gray-50 dark:hover:bg-gray-900"
				v-for="(user, i) in items.data"
				:key="`user_${i}`"
			>
				<div
					class="flex-grow-0 px-2 py-1 font-mono text-sm font-bold text-gray-400"
					style="min-width: 40px"
				>
					#{{ user.id }}
				</div>
				<div
					class="flex-1 px-2 py-1 font-semibold truncate"
					v-tooltip="user.email"
				>
					{{ user.email }}
				</div>
				<div
					class="flex-1 px-2 py-1 font-semibold truncate"
					v-tooltip="user.username"
				>
					{{ user.username }}
				</div>
				<div class="flex-1 px-2 py-1 truncate border-r min-h-8">
					{{ user.email_verified_at }}
				</div>

				<!-- company -->
				<div
					class="flex-1 px-2 py-1 bg-gray-50 min-h-8 group-hover:bg-gray-100"
				>
					{{ user.company != undefined ? user.company.name : "" }}
				</div>
				<div
					class="flex-1 px-2 py-1 truncate bg-gray-50 min-h-8 group-hover:bg-gray-100"
					v-tooltip="
						user.company != undefined ? user.company.description : ''
					"
				>
					{{ user.company != undefined ? user.company.description : "" }}
				</div>
				<div
					class="flex-1 px-2 py-1 truncate bg-gray-50 min-h-8 group-hover:bg-gray-100"
					v-tooltip="user.company != undefined ? user.company.url : ''"
				>
					{{ user.company != undefined ? user.company.url : "" }}
				</div>
				<div
					class="flex-1 px-2 py-1 bg-gray-50 min-h-8 group-hover:bg-gray-100"
				>
					{{ user.company != undefined ? user.company.coc : "" }}
				</div>
				<div
					class="flex-1 px-2 py-1 bg-gray-50 min-h-8 group-hover:bg-gray-100"
				>
					{{ user.company != undefined ? user.company.tax_nr : "" }}
				</div>

				<!-- address -->
				<!-- <pre>{{ user }}</pre> -->
				<!-- <div class="flex-1 px-2 py-1">
					{{ user.company != undefined ? user.company.name : "" }}
				</div>
				<div
					class="flex-1 px-2 py-1 truncate"
					v-tooltip="
						user.company != undefined ? user.company.description : ''
					"
				>
					{{ user.company != undefined ? user.company.description : "" }}
				</div>
				<div
					class="flex-1 px-2 py-1 truncate"
					v-tooltip="user.company != undefined ? user.company.url : ''"
				>
					{{ user.company != undefined ? user.company.url : "" }}
				</div>
				<div class="flex-1 px-2 py-1">
					{{ user.company != undefined ? user.company.coc : "" }}
				</div> -->
			</div>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		items: Object,
	},
};
</script>

<style>
</style>
