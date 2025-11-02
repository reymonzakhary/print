from resources.suppliers.categories.calculates.semiCalculationApi import semiCalculation
from resources.interface.calculationInterface import calculationInterface
from bson.json_util import dumps
import json
import copy
from flask import Response, request, jsonify


class collectionCalculation(calculationInterface):
    addonsOptions = None

    def __init__(self):
        self.addonsOptions = None

    def netPrice(self, category, product, quantity, addonsOptions) -> int:
        # return product[0]['prices']
        self.addonsOptions = list(addonsOptions)
        prices = {}
        if len(product) == 0:
            return prices
        for price in product[0]['prices']:
            if not price['tables']['pm'].lower() in prices.keys():
                prices[price['tables']['pm'].lower()] = []
            prices[price['tables']['pm'].lower()].append({
                'pm': price['tables']['pm'].lower(),
                'dlv': price['tables']['dlv']['days'],
                'prices': {
                    "start_cost": 0,
                    "subtotal": price['tables']['p'],
                    "total": price['tables']['p'],
                    "qty": price['tables']['qty']
                }
            })
        if len(self.addonsOptions) == 0:
            return prices

        for emptyOne in list(prices):
            if len(prices[emptyOne]) == 0:
                prices.pop(emptyOne)

        return self.generate_price(prices)

    def generate_price(self, items):
        result = {}
        for pm in items:
            result[pm] = {}
            for days in items[pm]:
                day = (f"day_" + str(days['dlv']))
                add_option = self.options_opt(days['dlv'], pm, days['prices']['qty'])
                # if pm in add_option.keys():
                if len(add_option):
                    if not day in result[pm].keys():
                        result[pm][day] = []
                    days['prices']['addons_subtotal'] = add_option[pm][day][0]['prices']["subtotal"]
                    days['prices']['addons_start_cost'] = add_option[pm][day][0]['prices']["start_cost"]
                    days['prices']['addons_total'] = add_option[pm][day][0]['prices']["total"]
                    days['prices']['subtotal'] = days['prices']['total']
                    days['prices']['total'] = days['prices']['total'] + days['prices']['addons_total']
                    result[pm][day].append(days)
        return result

    def options_opt(self, day, pm, quantity):
        category = Category(day, pm)
        semi_calculation_options = semiCalculation().netPrice(
            category,
            json.loads(dumps(self.addonsOptions)),
            quantity,
            []
        )
        result = {}
        for pm in semi_calculation_options:

            for days in semi_calculation_options[pm]:

                day = (f"day_" + str(days['dlv']))
                if not pm in result.keys():
                    result[pm] = {}

                if not day in result[pm].keys():
                    result[pm][day] = []
            result[pm][day].append(days)
        return result


class Category:
    start_cost = 0
    dlv_days = None
    printing_method = {}

    def __init__(self, day, pm):
        self.start_cost = 0
        self.dlv_days = [
            {
                "days": day,
                "value": 0
            }
        ]
        self.printing_method = [{
            "slug": pm
        }]
