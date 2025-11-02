<template>
	<div
		class="flex items-center w-full"
		:class="{ 'flex-wrap': mode === 2 }"
		v-if="data"
	>
		<div
			class="inline-flex items-center text-xs"
			v-if="mode === 1 || mode === 3"
		>
			<p
				class="hidden mr-2 font-bold tracking-tighter uppercase whitespace-nowrap md:flex"
			>
				{{ $t("disk space") }}
			</p>

			<p class="text-theme-100 whitespace-nowrap">
				<transition name="fade">
					<span class="font-bold text-white" v-if="data.total">
						{{ bytesToHuman(data.total) }}
					</span>
				</transition>
				<span class="hidden md:inline-block">
					{{ $t("from") }}
					<transition name="fade">
						<span class="font-bold text-white" v-if="data.disk">
							{{ bytesToHuman(data.disk) }}
						</span>
					</transition>
				</span>
				{{ $t("used") }}
			</p>
		</div>

		<div class="flex justify-between w-full" v-if="mode === 2">
			<span class="font-mono text-xs whitespace-nowrap" v-if="data.total">
				{{ bytesToHuman(data.total) }}
			</span>

			<span class="font-mono text-xs whitespace-nowrap" v-if="data.disk">
				{{ bytesToHuman(data.disk) }}
			</span>
		</div>

		<section
			class="flex w-full overflow-hidden text-xs rounded-full shadow-inner bg-theme-500"
			:class="[`h-${barHeight}`, { 'md:mx-12': mode === 1 }]"
		>
			<template v-for="(size, key, i) in data.sizes">
				<span
					v-if="mode === 1 || mode === 2"
					:key="'f-' + i"
					class="flex items-center justify-center"
					:class="{
						'bg-teal-400': i === 0,
						'bg-indigo-400': i === 1,
						'bg-purple-400': i === 2,
						'bg-pink-400': i === 3,
						'bg-orange-400': i === 4,
					}"
					:style="`width: ${(size / data.disk) * 100}%`"
					v-tooltip="key + ' ' + bytesToHuman(size)"
				>
					<p
						v-if="size / data.disk > 10"
						class="text-xs font-normal text-white"
					>
						{{ key }} {{ bytesToHuman(size) }}
					</p>
				</span>
			</template>

			<span
				class="px-2 ml-auto"
				v-if="mode === 1 && data.total && data.disk"
			>
				{{ bytesToHuman(data.disk - data.total) }}
				{{ $t("remaining") }}
			</span>
		</section>
	</div>
</template>

<script>
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
	mixins: [managerhelper, helper],
	props: {
		data: {
			required: true,
			type: Object,
		},
		mode: {
			required: true,
			type: Number,
		},
		barHeight: {
			required: false,
			default: 4,
			type: Number,
		},
	},
};
</script>

<style>
</style>