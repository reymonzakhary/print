const CategoryBoxOption = require("../Models/CategoryBoxOption");
const slugify = require('slugify');
const mongoose = require('mongoose');
const SupplierBox = require("../Models/SupplierBox");
const Box = require("../Models/Box");
const SupplierOption = require("../Models/SupplierOption");
const Option = require("../Models/Option");
const SupplierBoops = require("../Models/SupplierBoops");
const ObjectId = mongoose.Types.ObjectId
/**
 * Returns a default supplier boops object based on the given parameters.
 *
 * @param {Query<FlattenMaps<InferSchemaType<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>>> & {_id: Types.ObjectId}, Document<unknown, ObtainSchemaGeneric<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>, "TQueryHelpers">, InferSchemaType<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>>> & Omit<InferSchemaType<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>> & {_id: Types.ObjectId} & {__v?: number}, never> & ObtainSchemaGeneric<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>, "TVirtuals"> & ObtainSchemaGeneric<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>, "TInstanceMethods">, ObtainSchemaGeneric<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>, "TQueryHelpers">, InferSchemaType<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>>, "findOne", ObtainSchemaGeneric<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>, "TInstanceMethods">> & ObtainSchemaGeneric<module:mongoose.Schema<any, Model<any, any, any, any>, {}, {}, {}, {}, DefaultSchemaOptions, {shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}, HydratedDocument<FlatRecord<{shareable: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, startCost: {default: number, type: Number | NumberConstructor, required: boolean}, productionDays: {default: [], type: [String | StringConstructor], required: boolean}, displayName: {default: [], type: [String | StringConstructor], required: boolean}, refCategoryName: {default: string, type: String | StringConstructor, required: boolean}, additional: {default: {}, type: Object | ObjectConstructor, required: boolean}, description: {default: null, type: String | StringConstructor, required: boolean}, sort: {default: number, type: Number | NumberConstructor, required: boolean}, countries: {default: [], type: [String | StringConstructor], required: boolean}, published: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, media: {type: [Object | ObjectConstructor], required: boolean}, calculationMethod: {type: [String | StringConstructor], required: boolean}, createdAt: {default: Date.now | (() => number), type: Date | DateConstructor, required: boolean}, priceBuild: {default: {}, type: Object | ObjectConstructor, required: boolean}, hasManifest: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, name: {type: String | StringConstructor, required: boolean}, checked: {default: boolean, type: Boolean | BooleanConstructor}, refId: {default: string, type: String | StringConstructor, required: boolean}, sku: {default: string, type: String | StringConstructor, required: boolean}, systemKey: {unique: boolean, type: String | StringConstructor, required: boolean}, dlvDays: {default: [], type: [String | StringConstructor], required: boolean}, slug: {unique: boolean, type: String | StringConstructor, required: boolean}, hasProducts: {default: boolean, type: Boolean | BooleanConstructor, required: boolean}, printingMethod: {default: null, type: [String | StringConstructor], required: boolean}}>, {}>>, "TQueryHelpers">} category - The category of the supplier.
 * @param {object} supplier_category - The supplier category object.
 * @param {Array} languages - The languages supported by the supplier.
 * @returns {object} - The default supplier boops object.
 */
const supplier_boops_default = async (category, supplier_category, languages) => {
    return {
        ...supplier_category._doc,
        ref_boops_name: '',
        ref_id: '',
        shareable: false,
        slug: supplier_category.slug,
        boops: await (prepare_standard_boops(category, languages)),
        tenant_id: supplier_category.tenant_id,
        tenant_name: supplier_category.tenant_name
    }
};

/**
 * Prepares standard boops based on category and languages.
 *
 * @param {Object} category - Category object.
 * @param {Array} languages - Array of languages.
 * @returns {Array} - Array of prepared standard boops.
 */
const prepare_standard_boops = async (category, languages) => {
    const boops = await CategoryBoxOption.aggregate([
        {
            $match: {
                "category": category._id
            }
        },
        {
            $lookup: {
                from: "boxes",
                foreignField: "_id",
                localField: "box",
                as: "box",
            },
        },
        {
            $lookup: {
                from: "options",
                foreignField: "_id",
                localField: "option",
                as: "option",
            },
        },
        {
            $project: {
                boxes: "$box",
                options: "$option",
            },
        }
    ])
    let box_obj = {}
    boops.forEach((bo) => {
        // Skip processing for specific slugs
        const boxSlug = bo.boxes[0].slug;
        if (boxSlug === "delivery-type" || boxSlug === "printing-process") {
            return; // Skip to the next iteration
        }

        if (!box_obj[boxSlug]) {
            const box_display_name = generate_display_name(languages, bo.boxes[0].name);
            box_obj[boxSlug] = {
                id: bo.boxes[0]._id,
                sort: 0,
                name: bo.boxes[0].name,
                system_key: bo.boxes[0].name,
                display_name: box_display_name,
                slug: slugify(bo.boxes[0].name, {lower: true}),
                media: [],
                input_type: "",
                published: false,
                unit: "mm",
                maximum: "",
                minimum: "",
                incremental_by: "",
                information: "",
                ops: []
            };
        }

        let opt_display_name = generate_display_name(languages, bo.options[0].name);

        box_obj[boxSlug]['ops'].push({
            id: bo.options[0]._id,
            name: bo.options[0].name,
            system_key: bo.options[0].name,
            display_name: opt_display_name,
            slug: bo.options[0].slug,
            dynamic: false,
            dynamic_keys: [],
            start_on: 0,
            end_on: 0,
            generate: false,
            dynamic_type: '',
            input_type: "",
            published: false,
            excludes: []
        });
    });

    // After this, sort the ops as needed and prepare the result
    const result = [];

    // Sort the boxes alphabetically by slug
    const sortedBoxKeys = Object.keys(box_obj).sort((a, b) => {
        return a.localeCompare(b); // Alphabetical sorting of box slugs
    });

    // Populate the result array with sorted boxes
    sortedBoxKeys.forEach(boxSlug => {
        // Sort the 'ops' array for each box by 'slug' in ascending alphabetical order
        box_obj[boxSlug].ops.sort((a, b) => {
            return a.slug.localeCompare(b.slug); // Alphabetical sorting of ops
        });

        // Add the sorted box object to the result array
        result.push(box_obj[boxSlug]);
    });


    return result;
}

/**
 * Generates an array of objects containing language ISO code and display name.
 * @param {Array<string>} language - The array of language ISO codes.
 * @param {string} name - The display name to be assigned to each language.
 * @returns {Array<object>} - The generated array of language objects with ISO code and display name.
 */
const generate_display_name = (language, name) => {
    return language.map((lang) => ({
        iso: lang,
        display_name: name

    }))
}

/**
 * Updates the display name for a specific language based on the given ISO code.
 * @param {Array<{display_name: string, iso: string}>} display_name - The array of language display names and ISO codes to update.
 * @param {string} name - The new display name to set.
 * @param {string} iso - The ISO code of the language to update.
 * @returns {Array<{display_name: string, iso: string}>} - The updated array of language display names and ISO codes.
 */
const update_display_name = (display_name, name, iso) => {
    return display_name.map(lang => {
        return lang.iso === iso ? {
            display_name: name,
            iso: iso,
        } : lang;
    })
}

/**
 * Check if a given string is a valid MongoDB ObjectId.
 * @param {string} str - The string to be checked
 * @returns {boolean} - True if the string is a valid ObjectId, false otherwise
 */
const isValidObjectId = (str) => {
    return mongoose.Types.ObjectId.isValid(str);
};

const getCategorySpecificConfig = (option, categoryId) => {
    if (!option.configure || !Array.isArray(option.configure)) {
        return {};
    }

    const categoryConfig = option.configure.find(config =>
        config.category_id && config.category_id.toString() === categoryId.toString()
    );

    return categoryConfig ? categoryConfig.configure : {};
};

/**
 * Retrieves the runs specific to a given category ID from the provided option object.
 * @param {Object} option - The option object containing runs to filter through.
 * @param {string} categoryId - The ID of the category for which specific runs are to be fetched.
 * @returns {array} - An array of runs specific to the given category ID, or an empty array if no runs are found.
 */
const getCategorySpecificRuns = (option, categoryId) => {
    if (!option.runs || !Array.isArray(option.runs)) {
        return [];
    }

    const categoryRun = option.runs.find(run =>
        run.category_id && run.category_id.toString() === categoryId.toString()
    );

    return categoryRun ? categoryRun.runs : [];
};

/**
 * Validates linked IDs in the given object and cleans up invalid IDs by setting them to null.
 *
 * @param {Object} obj The object containing linked IDs to be validated and cleaned.
 * @param {Object} models The MongoDB collections used for querying linked IDs.
 * @param {Object} [sourceModels=null] Optional source models to update with cleaned linked IDs.
 *
 * @return {Promise<{result: Object}>} A Promise that resolves with the cleaned object.
 */
async function validateLinkedIdsAndClean(obj, models, sourceModels = null) {
    // Check if obj exists and is valid
    if (!obj || typeof obj !== 'object') {
        console.warn('validateLinkedIdsAndClean: Invalid or undefined object provided');
        return obj; // Return as-is if invalid
    }

    // Collect all linked IDs from the object
    const linkedIds = [];
    const boopLinkedIds = [];
    const optionLinkedIds = [];

    // Arrays to track what needs to be updated in source models
    const invalidMainIds = [];
    const invalidBoopIds = [];
    const invalidOptionIds = [];

    // Main object linked ID
    if (obj.linked) linkedIds.push(obj.linked);

    // Collect boop and option linked IDs
    if (obj.boops) {
        obj.boops.forEach(boop => {
            if (boop && boop.linked) boopLinkedIds.push(boop.linked);
            if (boop && boop.ops) {
                boop.ops.forEach(option => {
                    if (option && option.linked) optionLinkedIds.push(option.linked);
                });
            }
        });
    }

    // Helper function to safely query collections (works with native MongoDB collections)
    async function safeFind(collection, ids) {
        if (!collection || !ids.length) return [];
        try {
            // Use native MongoDB collection methods
            return await collection.find({ _id: { $in: ids } }, { projection: { _id: 1 } }).toArray();
        } catch (error) {
            console.error('Error querying collection:', error);
            return [];
        }
    }

    // Batch check all IDs in parallel
    const [validMainIds, validBoopIds, validOptionIds] = await Promise.all([
        safeFind(models.categories, linkedIds),
        safeFind(models.boxes, boopLinkedIds),
        safeFind(models.options, optionLinkedIds)
    ]);

    // Create Sets for O(1) lookup
    const validMainSet = new Set(validMainIds.map(doc => doc._id.toString()));
    const validBoopSet = new Set(validBoopIds.map(doc => doc._id.toString()));
    const validOptionSet = new Set(validOptionIds.map(doc => doc._id.toString()));

    // Clean the object
    const result = { ...obj };

    // Set invalid main linked ID to null and track for source model update
    if (obj.linked && !validMainSet.has(obj.linked.toString())) {
        console.log(`Setting invalid main linked ID to null: ${obj.linked}`);
        invalidMainIds.push(obj.linked);
        result.linked = null;
    }

    // Clean boops - keep all boops but set invalid linked IDs to null
    if (obj.boops) {
        result.boops = obj.boops.map(boop => {
            const cleanedBoop = { ...boop };

            // Set invalid boop linked ID to null and track for source model update
            if (boop.linked && !validBoopSet.has(boop.linked.toString())) {
                console.log(`Setting invalid boop linked ID to null for "${boop.name}": ${boop.linked}`);
                invalidBoopIds.push(boop.linked);
                cleanedBoop.linked = null;
            }

            // Clean options - keep all options but set invalid linked IDs to null
            if (boop.ops && Array.isArray(boop.ops)) {
                cleanedBoop.ops = boop.ops.map(option => {
                    const cleanedOption = { ...option };

                    // Set invalid option linked ID to null and track for source model update
                    if (option.linked && !validOptionSet.has(option.linked.toString())) {
                        console.log(`Setting invalid option linked ID to null for "${option.name}": ${option.linked}`);
                        invalidOptionIds.push(option.linked);
                        cleanedOption.linked = null;
                    }

                    return cleanedOption;
                });
            }

            return cleanedBoop;
        });
    }

    // Update source models to remove invalid linked IDs
    if (sourceModels && (invalidMainIds.length || invalidBoopIds.length || invalidOptionIds.length)) {
        console.log(`Found invalid IDs - updating source models...`);
        console.log(`Object ID: ${obj._id}`);
        console.log(`Invalid counts - Main: ${invalidMainIds.length}, Boops: ${invalidBoopIds.length}, Options: ${invalidOptionIds.length}`);

        await updateSourceModels(sourceModels, {
            invalidMainIds,
            invalidBoopIds,
            invalidOptionIds
        }, obj._id);
    } else {
        console.log('No invalid IDs found or no source models provided - skipping database updates');
    }

    return result;
}

/**
 * Update the given models based on invalid IDs and source object ID.
 *
 * @param {Object} sourceModels - The source models to update.
 * @param {Object} invalidIds - Object containing arrays of invalid IDs.
 * @param {string} sourceObjectId - The ID of the source object.
 * @returns {Promise<{result: Error}>} A promise that resolves to an object with a result field containing potential errors.
 */
async function updateSourceModels(sourceModels, invalidIds, sourceObjectId) {
    const updatePromises = [];

    // Update supplierCategory if main linked IDs are invalid
    if (sourceModels.supplierCategory && invalidIds.invalidMainIds.length) {
        console.log(`Updating supplierCategory to set invalid linked IDs to null for object ${sourceObjectId}`);
        updatePromises.push(
            sourceModels.supplierCategory.updateOne(
                { _id: sourceObjectId },
                { $set: { linked: null } }
            )
        );
    }

    // Update supplierBoxes if boop linked IDs are invalid
    if (sourceModels.supplierBoxes && invalidIds.invalidBoopIds.length) {
        console.log(`Updating supplierBoxes to set invalid linked IDs to null`);
        console.log(`Invalid boop IDs:`, invalidIds.invalidBoopIds);
        // Update all boxes with invalid linked IDs
        updatePromises.push(
            sourceModels.supplierBoxes.updateMany(
                { linked: { $in: invalidIds.invalidBoopIds } },
                { $set: { linked: null } }
            )
        );
    }

    // Update supplierOptions if option linked IDs are invalid
    if (sourceModels.supplierOptions && invalidIds.invalidOptionIds.length) {
        console.log(`Updating supplierOptions to set invalid linked IDs to null`);
        console.log(`Invalid option IDs:`, invalidIds.invalidOptionIds);
        // Update all options with invalid linked IDs
        updatePromises.push(
            sourceModels.supplierOptions.updateMany(
                { linked: { $in: invalidIds.invalidOptionIds } },
                { $set: { linked: null } }
            )
        );
    }

    try {
        const results = await Promise.all(updatePromises);
        console.log('Source models updated successfully');
        console.log('Update results:', results);
        return results;
    } catch (error) {
        console.error('Error updating source models:', error);
        throw error;
    }
}

/**
 * Merge display names from incoming array into default array.
 * @param {Array} defaultArray - The default array of objects containing ISO codes and display names.
 * @param {Array} incomingArray - The incoming array of objects containing ISO codes and display names to merge.
 * @returns {Array} - The merged array of objects with updated display names.
 */
const mergeDisplayNames = (defaultArray, incomingArray) => {
    // Convert default array to Map for O(1) lookups
    const map = new Map(defaultArray.map(item => [item.iso, item]));

    // Update/add incoming items
    incomingArray.forEach(incoming => {
        map.set(incoming.iso, {
            iso: incoming.iso,
            display_name: incoming.display_name
        });
    });

    // Convert back to array
    return Array.from(map.values());
}

async function _processBoxes(boxes, supplierId, tenantName, iso, supplierCategory) {
    const processedBoxes = [];

    for (const box of boxes) {
        // Validate box has options
        if (!box.ops || box.ops.length === 0) {
            throw new Error(`Box ${box.name} does not have any options`);
        }

        // Create or get supplier box
        const supplierBox = await _upsertSupplierBox(box, supplierId, tenantName); // Remove extra parentheses

        // Process all options for this box - ADD AWAIT HERE
        const processedOptions = await _processOptions(
            box.ops,
            supplierId,
            tenantName,
            supplierCategory,
            box
        );

        // Build box object with processed options
        const boxObject = _buildBoxObject(box, supplierBox, iso, processedOptions); // No await needed - not async
        processedBoxes.push(boxObject);
    }

    return processedBoxes;
}

// Helper: Create or update supplier box
async function _upsertSupplierBox(box, supplierId, tenantName) {
    const existingBox = await SupplierBox.findOne({
        tenant_id: supplierId,
        $or: [
            { _id: new ObjectId(box.id) },
            { linked: new ObjectId(box.id) },
            { slug: box.slug },
        ]
    });

    if (existingBox) {
        return existingBox;
    }

    // Check if system box exists
    const systemBox = await Box.findOne({
        _id: { $in: [new ObjectId(box.id), new ObjectId(box.linked)] }
    });

    const boxData = {
        tenant_id: supplierId,
        tenant_name: tenantName,
        additional: box.additional,
        name: systemBox ? systemBox.name : box.name,
        source_slug: box.source_slug,
        display_name: box.display_name,
        system_key: box.system_key || slugify(box.name, { lower: true }),
        description: box.description || '',
        slug: box.slug,
        iso: box.iso || '',
        media: box.media || [],
        sqm: box.sqm || false,
        appendage: box.appendage || false,
        calculation_type: box.calculation_type || '',
        input_type: box.input_type,
        linked: systemBox ? systemBox._id : null,
        published: true,
    };

    return SupplierBox.create(boxData); // Removed redundant await
}

// Helper: Process all options for a box
async function _processOptions(options, supplierId, tenantName, supplierCategory, parentBox) {
    const processedOptions = [];

    for (const option of options) {
        // Create or update supplier option - ADD AWAIT HERE
        // await _upsertSupplierOption(option, supplierId, tenantName, supplierCategory, parentBox);

        // Get the supplier option
        const supplierOption = await SupplierOption.findOne({
            tenant_id: supplierId,
            $or: [
                { _id: new ObjectId(option.id) },
                { linked: new ObjectId(option.id) },
                { slug: option.slug }
            ]
        });

        if (!supplierOption) {
            throw new Error(`Option ${option.name} not found after upsert`);
        }

        // Build option object
        const optionObject = _buildOptionObject(option, supplierOption, supplierCategory); // No await needed
        processedOptions.push(optionObject);
    }

    return processedOptions;
}

// Helper: Create or update supplier option
async function _upsertSupplierOption(option, supplierId, tenantName, supplierCategory, parentBox) {
    const systemOption = await Option.findOne({ slug: option.slug });

    const configure = {
        category_id: supplierCategory._id,
        configure: {
            start_cost: option.start_cost || 0,
            unit: option.unit || systemOption?.unit || 'mm',
            width: option.width || systemOption?.width || 0,
            maximum_width: option.maximum_width || systemOption?.maximum_width || 0,
            minimum_width: option.minimum_width || systemOption?.minimum_width || 0,
            height: option.height || systemOption?.height || 0,
            maximum_height: option.maximum_height || systemOption?.maximum_height || 0,
            minimum_height: option.minimum_height || systemOption?.minimum_height || 0,
            length: option.length || 0,
            maximum_length: option.maximum_length || systemOption?.maximum_length || 0,
            minimum_length: option.minimum_length || systemOption?.minimum_length || 0,
            dimension: option.dimension || systemOption?.dimension || '2d',
            incremental_by: option.incremental_by || 0,
            dynamic: option.dynamic || false,
            dynamic_keys: option.dynamic_keys || [],
            start_on: option.start_on || 0,
            end_on: option.end_on || 0,
            dynamic_type: option.type || '',
            generate: option.generate || false,
            calculation_method: option.calculation_method || 'qty'
        }
    };

    return SupplierOption.findOneAndUpdate( // Removed redundant await
        {
            tenant_id: supplierId,
            $or: [
                { _id: new ObjectId(option.id) },
                { linked: new ObjectId(option.id) },
                { slug: option.slug }
            ]
        },
        {
            $set: {
                tenant_id: supplierId,
                tenant_name: tenantName,
                name: systemOption ? systemOption.name : option.name,
                display_name: option.display_name,
                system_key: option.system_key || slugify(option.name, { lower: true }),
                slug: option.slug,
                source_slug: option.source_slug,
                description: option.description || '',
                has_children: parentBox.has_children || option.has_children || false,
                extended_fields: parentBox.extended_fields || option.extended_fields || [],
                parent: parentBox.parent || option.parent || null,
                boxes: parentBox.boxes || option.boxes || [],
                // sheet_runs: option.sheet_runs || [],
                rpm: option.rpm || 0,
                information: option.information || '',
                input_type: option.input_type || systemOption?.input_type || '',
                linked: systemOption ? systemOption._id : null,
            },
            $addToSet: { configure }
        },
        { upsert: true, new: true }
    );
}

// Helper: Build box object for response
const _buildBoxObject = (box, supplierBox, iso, processedOptions) => {
    const linkedBox = supplierBox.linked && isValidObjectId(supplierBox.linked) && supplierBox.linked !== ''
        ? new ObjectId(supplierBox.linked)
        : null;

    return {
        id: supplierBox._id,
        iso: iso || '',
        name: box.name,
        display_name: box.display_name,
        system_key: box.system_key || slugify(box.name, { lower: true }),
        source_slug: supplierBox.source_slug,
        slug: box.slug,
        description: box.description || '',
        ref_box: box.ref_box ? new ObjectId(box.ref_box) : '',
        sqm: box.sqm || false,
        additional: box.additional,
        appendage: box.appendage || false,
        calculation_type: box.calculation_type || '',
        media: box.media || [],
        input_type: box.input_type,
        linked: linkedBox,
        published: true,
        divider: box.divider || '',
        ops: processedOptions,
    };
}

// Helper: Build option object for response
const _buildOptionObject = (option, supplierOption, supplierCategory) => {

    const configure = supplierOption.configure?.find(
        c => c.category_id.toString() === supplierCategory._id.toString()
    )?.configure || {};

    const linkedOption = supplierOption.linked && isValidObjectId(supplierOption.linked) && supplierOption.linked !== ''
        ? new ObjectId(supplierOption.linked)
        : null;

    return {
        id: supplierOption._id,
        ref_option: option.ref_option ? new ObjectId(option.ref_option) : null,
        name: option.name,
        display_name: option.display_name,
        system_key: option.system_key || slugify(option.name, { lower: true }),
        slug: option.slug,
        description: option.description || '',
        media: option.media || [],
        sheet_runs: option.sheet_runs || [],
        rpm: option.rpm || 0,
        information: option.information || '',
        linked: linkedOption,
        excludes: option.excludes || [],
        input_type: option.input_type || '',
        source_slug: supplierOption.source_slug,
        dimension: configure.dimension || '2d',
        dynamic: configure.dynamic || false,
        unit: configure.unit || 'mm',
        width: configure.width || 0,
        maximum_width: configure.maximum_width || 0,
        minimum_width: configure.minimum_width || 0,
        height: configure.height || 0,
        maximum_height: configure.maximum_height || 0,
        minimum_height: configure.minimum_height || 0,
        length: configure.length || 0,
        maximum_length: configure.maximum_length || 0,
        minimum_length: configure.minimum_length || 0,
        start_cost: configure.start_cost || 0,
        dynamic_keys: configure.dynamic_keys || [],
        start_on: configure.start_on || 0,
        end_on: configure.end_on || 0,
        dynamic_type: configure.dynamic_type || '',
        generate: configure.generate || false,
        dynamic_object: configure.dynamic_object,
    };
}

// Helper: Create or update supplier boops
async function _upsertSupplierBoops(supplierCategory, supplierId, tenantName, divided, boxes) {
    const linkedCategory = supplierCategory.linked && isValidObjectId(supplierCategory.linked) && supplierCategory.linked !== ''
        ? new ObjectId(supplierCategory.linked)
        : null;

    const existingBoops = await SupplierBoops.findOne({
        supplier_category: supplierCategory._id,
        tenant_id: supplierId,
    });

    if (!existingBoops) {
        const newBoops = await SupplierBoops.create({
            tenant_id: supplierId,
            tenant_name: tenantName,
            supplier_category: supplierCategory._id,
            linked: linkedCategory,
            name: supplierCategory.name,
            system_key: supplierCategory.system_key || slugify(supplierCategory.system_key, { lower: true }),
            display_name: supplierCategory.display_name,
            slug: supplierCategory.slug,
            divided: divided || false,
            boops: boxes,
        });

        return { data: newBoops, isNew: true };
    }
    await SupplierBoops.updateOne(
        { supplier_category: supplierCategory._id, tenant_id: supplierId },
        {
            boops: boxes,
            linked: linkedCategory,
            divided: divided || false,
        }
    );

    const updatedBoops = await SupplierBoops.findOne({
        supplier_category: supplierCategory._id,
        tenant_id: supplierId,
    });

    return { data: updatedBoops, isNew: false };
}

/**
 * Validates that all boxes and options exist in their respective collections
 * @param {Array} boops - Array of boops containing boxes and their options
 * @param {string} supplier_id - The tenant ID to validate against
 * @returns {Promise<Array>} - Array of validation error messages (empty if all valid)
 */
async function validateBoopsExistence(boops, supplier_id) {
    const validationErrors = [];
    
    for (const boop of boops) {
        // Check if box exists
        const boxExists = await SupplierBox.findOne({
            _id: new ObjectId(boop.id),
            tenant_id: supplier_id
        });
        
        if (!boxExists) {
            validationErrors.push(`Box with id ${boop.id} not found`);
            continue;
        }
        
        // Check if all ops exist
        if (boop.ops && Array.isArray(boop.ops)) {
            for (const op of boop.ops) {
                const optionExists = await SupplierOption.findOne({
                    _id: new ObjectId(op.id),
                    tenant_id: supplier_id
                });
                
                if (!optionExists) {
                    validationErrors.push(`Option with id ${op.id} not found`);
                }
            }
        }
    }
    
    return validationErrors;
}

module.exports = {
    supplier_boops_default,
    prepare_standard_boops,
    generate_display_name,
    update_display_name,
    isValidObjectId,getCategorySpecificConfig,getCategorySpecificRuns,
    validateLinkedIdsAndClean,
    mergeDisplayNames, _processBoxes, _upsertSupplierBoops, _buildOptionObject, _buildBoxObject,
    _upsertSupplierOption, _processOptions, _upsertSupplierBox, validateBoopsExistence
}
