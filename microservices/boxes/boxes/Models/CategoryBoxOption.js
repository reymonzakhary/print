const mongoose = require('mongoose');
const { Schema } = mongoose;

// Define the schema
const CategoryBoxOptionSchema = new Schema({
    category: { type: Schema.Types.ObjectId, ref: 'Category', required: true },
    box: { type: Schema.Types.ObjectId, ref: 'Box', required: true },
    option: { type: Schema.Types.ObjectId, ref: 'Option', required: true }
}, {
    collection: 'category_box_options'
}, {
  versionKey: false // You should be aware of the outcome after set to false
});

// Create the model
module.exports = CategoryBoxOption = mongoose.model('category_box_options', CategoryBoxOptionSchema);
