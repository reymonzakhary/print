import SEOPreview from './SEOPreview.vue';

export default {
    title: 'CMS/SEO/SEOPreview',
    component: SEOPreview,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { SEOPreview },
    template: `<div class="w-96"><SEOPreview v-bind="$props" v-on="$listeners" /></div>`,
});

export const Google = Story.bind({});
Google.args = {
    variant: 'google',
    title: 'Free Money | Toeslagen - Gemeente Beverwijk',
    url: 'https://www.beverwijk.nl/free-money',
    description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla vitae elit libero, a pharetra augue. Donec ullamcorper nulla non metus auctor fringilla. Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.',
};

export const Facebook = Story.bind({});
Facebook.args = {
    variant: 'facebook',
    title: 'Free Money | Toeslagen - Gemeente Beverwijk',
    url: 'https://www.beverwijk.nl/free-money',
    description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla vitae elit libero, a pharetra augue. Donec ullamcorper nulla non metus auctor fringilla. Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.',
    image: 'https://images.unsplash.com/photo-1682685797439-a05dd915cee9?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDF8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
};

export const FacebookNoImage = Story.bind({});
FacebookNoImage.args = {
    variant: 'facebook',
    title: 'Free Money | Toeslagen - Gemeente Beverwijk',
    url: 'https://www.beverwijk.nl/free-money',
    description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla vitae elit libero, a pharetra augue. Donec ullamcorper nulla non metus auctor fringilla. Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.',
};

export const X = Story.bind({});
X.args = {
    variant: 'x',
    title: 'Free Money | Toeslagen - Gemeente Beverwijk',
    url: 'https://www.beverwijk.nl/free-money',
    description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla vitae elit libero, a pharetra augue. Donec ullamcorper nulla non metus auctor fringilla. Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.',
    image: 'https://images.unsplash.com/photo-1682685797439-a05dd915cee9?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDF8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
};

export const XNoImage = Story.bind({});
XNoImage.args = {
    variant: 'x',
    title: 'Free Money | Toeslagen - Gemeente Beverwijk',
    url: 'https://www.beverwijk.nl/free-money',
    description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla vitae elit libero, a pharetra augue. Donec ullamcorper nulla non metus auctor fringilla. Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.',
};