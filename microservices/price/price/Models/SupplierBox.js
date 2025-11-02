const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierBoxSchema = Schema({
    iso:{type: String, required: true},
    row_id:{type: String, required: false},
    sort:{type: Number, required: false, default: 0},
    tenant_id:{type: String, required: true},
    tenant_name:{type: String, required: false},
    sku:{type: String, required: false, default:''},
    name:{type: String }, // = db.String(required=True, default='', unique_with=('tenant_id'))
    display_name:{type: [], required: true}, // = db.[](required=True, default=[])
    system_key:{type: String, required: true}, // = db.String(required=True)
    input_type:{type: String, required: false}, // = db.String(required=False, default='')
    incremental:{type: Boolean, required: true}, // = db.Boolean(required=True, default=False)
    select_limit:{type: Number, required: true}, // = db.Number(required=True, default=0)
    option_limit:{type: Number, required: true}, // = db.Number(required=True, default=0)
    sqm:{type: Boolean, required: true}, // = db.Boolean(required=True, default=False)
    slug:{type: String, required: true}, // = db.String(required=True, default='', unique_with=('tenant_id'))
    appendage:{type: String, required: true}, // = db.String(required=True, default='', unique_with=('tenant_id'))
    calculation_type:{type: String, required: true}, // = db.String(required=True, default='', unique_with=('tenant_id'))
    // linked:{type: ReferenceField}, // = db.ReferenceField(Box)
    description:{type: String, required: false}, // = db.String(required=False, default='')
    media:{type: []}, // = db.[]()
    shareable:{type: Boolean, default: true}, // = db.Boolean(default=True)
    published:{type: Boolean, default: true}, // = db.Boolean(default=True)
    created_at:{type: Date, required: true}, // = db.DateTimeField(default=datetime.datetime.now, required=True)
    // additional:{type: DictField, required: true}, // = db.DictField(required=False, default={})
    additional:{type: String, required: true},
});


module.exports = SupplierBox = mongoose.model("supplier_boxes", SupplierBoxSchema);
