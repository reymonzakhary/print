import UIImageSelector from './UIImageSelector.vue';

export default {
    title: 'global/ui/UIImageSelector',
    component: UIImageSelector,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UIImageSelector },
    template: `<UIImageSelector v-bind="$props" v-on="$listeners" />`,
});

export const NotSelected = Story.bind({});
NotSelected.args = {
    // props
};

export const Selected = Story.bind({});
Selected.args = {
    // props
    selectedImage: 'https://picsum.photos/200/300',
};