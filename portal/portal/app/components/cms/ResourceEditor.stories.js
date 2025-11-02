import ResourceEditor from './ResourceEditor.vue'

export default {
    title: 'CMS/Organisms/ResourceEditor',
    component: ResourceEditor,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ResourceEditor },
    template: `<div id="__layout"><div><ResourceEditor v-bind="$props" v-on="$listeners" /></div></div>`,
})

export const Default = Story.bind({})
Default.args = {
    isLoading: false,
    selectedResource: 69,
    resource: {
        id: 1,
        navigation: {
            manager_title: '',
            menu_title: '',
            url: ''
        },
        seo: {
            title: '',
            image: null,
            description: ''
        },
        locked: false
    },
}

export const isEmpty = Story.bind({})
isEmpty.args = {
    isLoading: false,
    editableResource: null,
}

export const isLoading = Story.bind({})
isLoading.args = {
    editableResource: null,
    selectedResource: 69,
    isLoading: true,
}

export const isLocked = Story.bind({})
isLocked.args = {
    ...Default.args,
    resource: {
        ...Default.args.resource,
        locked: true,
    },
}