import UIGallerySelector from './UIGallerySelector.vue';

export default {
    title: 'Global/ui/UIGallerySelector',
    component: UIGallerySelector,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UIGallerySelector },
    template: `<UIGallerySelector v-bind="$props" v-on="$listeners" />`,
});

export const Empty = Story.bind({});
Empty.args = {
    // props
};

export const WithItems = Story.bind({});
WithItems.args = {
    // props
    selectedImages: [
        {
            path: 'https://picsum.photos/200/300',
            imageUrl: 'https://picsum.photos/200/300',
            id: Math.floor(Math.random() * 100)
        },
        {
            path: 'https://picsum.photos/201/300',
            imageUrl: 'https://picsum.photos/201/300',
            id: Math.floor(Math.random() * 100)
        },
        {
            path: 'https://picsum.photos/202/300',
            imageUrl: 'https://picsum.photos/202/300',
            id: Math.floor(Math.random() * 100)
        },
        {
            path: 'https://picsum.photos/203/300',
            imageUrl: 'https://picsum.photos/203/300',
            id: Math.floor(Math.random() * 100)
        },
    ]
};

export const WithLimit = Story.bind({});
WithLimit.args = {
    // props
    selectedImages: [
        {
            path: 'https://picsum.photos/200/300',
            imageUrl: 'https://picsum.photos/200/300',
            id: Math.floor(Math.random() * 100)
        },
        {
            path: 'https://picsum.photos/201/300',
            imageUrl: 'https://picsum.photos/201/300',
            id: Math.floor(Math.random() * 100)
        }
    ],
    limit: 2
};