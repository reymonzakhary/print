<template>
	<VDropdown>
		<!-- This will be the popover target (for the events and position) -->
		<button class="text-sm">
			<font-awesome-icon class="fa-fw" :icon="['fal', 'circle-info']" />
		</button>
		<!-- This will be the content of the popover -->
		<template #popper>
			<div
				class="flex w-full p-2 text-sm text-white bg-blue-400 rounded-md shadow-md"
			>
				<ul class="w-1/2 font-bold divide-y divide-black w-80">
					<li class="flex w-full py-2">
						<div class="w-1/2">
							<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								ctrl </span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
								>S</span
							>
						</div>
						<p class="w-1/2">
							<span class="text-green-300"><font-awesome-icon class="fa-fw" :icon="['fal', 'floppy-disk']" /> Save</span>
						</p>
					</li>
					<li class="flex w-full py-2">
						<div class="w-1/2">
							<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								ctrl </span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								space
							</span>
						</div>
						<p class="w-1/2"><font-awesome-icon class="fa-fw" :icon="['fal', 'tags']" /> List of tags</p>
					</li>
					<li class="flex w-full py-2">
						<div class="w-1/2">
							<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								ctrl </span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								F
							</span>
						</div>
						<p class="w-1/2"><font-awesome-icon class="fa-fw" :icon="['fal', 'file-search']" /> Find</p>
					</li>
					<li class="flex w-full py-2">
						<div class="w-1/2">
							<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								ctrl </span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								H
							</span>
						</div>
						<p class="w-1/2"><font-awesome-icon class="fa-fw" :icon="['fal', 'exchange']" /> Find &amp; replace</p>
					</li>
					<li class="flex w-full py-2">
						<div class="w-1/2">
							<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								ctrl </span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								Z
							</span>
						</div>
						<p class="w-1/2"><font-awesome-icon class="fa-fw" :icon="['fal', 'arrow-rotate-left']" /> Undo</p>
					</li>
					<li class="flex w-full py-2">
						<div class="w-1/2">
							<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								ctrl </span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
								>shift</span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
								>Z</span
							>
						</div>
						<p class="w-1/2"><font-awesome-icon class="fa-fw" :icon="['fal', 'arrow-rotate-right']" /> Redo</p>
					</li>
					<li class="flex w-full py-2">
						<div class="w-1/2">
							<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
							>
								ctrl </span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
								>shift</span
							>+<span
								class="px-1 mx-1 text-xs text-gray-500 bg-gray-100 border border-b-4 border-l-2 border-r-2 border-gray-300 rounded-sm"
								>I</span
							>
						</div>
						<p class="w-1/2"><font-awesome-icon class="fa-fw" :icon="['fal', 'align-left']" /> Format document</p>
					</li>
				</ul>

				<section class="p-2 pl-4 border-l border-blue-500">
					<h1 class="text-lg">Create a dynamic template</h1>
					<ol class="pl-4 space-y-4 list-decimal list-outside">
						<li>
							Write up or paste your template html
						</li>
						<li>
							Replace your text with one of Prindustry's tags (ctrl + space) that corresponds with your content type. <br><br>
                     E.G.: title = [[$text]]. This will create a normal textinput in your resource manager. <br>
                     E.G.: paragraph = [[$textarea]]. <br>
                     E.G.: large content area = [[$richtext]]. This will give you a wysiwyg editor in the resource manager. <br>
						</li>
						<li>
							Replace your text with one of Prindustry's tags (ctrl + space)
						</li>
						<li>
							Save your document (ctrl + S)
						</li>
						<li>
							Select the template in your resource
						</li>
					</ol>
				</section>
			</div>
		</template>
	</VDropdown>
</template>

<script>
export default {};
</script>

<style>
</style>