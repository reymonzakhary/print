import json
from bson.json_util import dumps
from flask import Response, request, jsonify

from resources.interface.calculationInterface import calculationInterface


class semiCalculation(calculationInterface):
    delivery_days_available = {}
    category_start_cost = 0
    quantity = 0
    result = {}
    pms = []
    num_options = 0

    def __init__(self):
        self.runs_generated = []
        self.delivery_days_available = {}

    def get_runs(self, data, qty):
        return filter(lambda i: i['from'] <= qty <= i['to'], data)

    def netPrice(self, category, options, quantity, addonsOptions) -> int:
        # Make Calculations
        self.quantity = quantity
        self.category_start_cost = category.start_cost
        self.num_options = len(options[0]['options']) if len(options) else 0
        for dlv_days in category.dlv_days:
            self.delivery_days_available[f"day_" + str(dlv_days['days'])] = dlv_days['value']

        for pm in category.printing_method:
            self.pms.append(pm['slug'])
        # loop on options to get each option price
        self.result["qty"] = self.quantity
        self.result["start_cost"] = self.category_start_cost
        self.result["pm"] = {}
        for pm in self.pms:
            self.result["pm"][pm] = {}
        if len(options):
            for option in options[0]['options']:
                self.generate_run_price(option)

        return self.generate_price()

    def generate_price(self):
        result = {}
        for pm in self.result["pm"]:
            for day in self.result["pm"][pm]:
                if len(self.result["pm"][pm][day]) != self.num_options:
                    continue
                start_cost = 0
                cost = 0
                for run in self.result["pm"][pm][day]:
                    start_cost += run['start_cost']
                    cost += (run['add_price']+run['ppp']) * self.quantity
                if not pm in result.keys():
                    result[pm] = []

                result[pm].append({
                    "pm": pm,
                    "dlv": day.replace('day_', ''),
                    "prices": {
                        "start_cost": start_cost+self.result['start_cost'],
                        "subtotal": cost,
                        "total": start_cost+cost+self.result['start_cost'],
                        "qty": self.result['qty']
                    }
                })
        return result

    def generate_run_price(self, option):
        option_run = list(self.get_runs(option['runs'], self.quantity))
        for run in option_run:
            for opt_pm in run['pm']:
                # check if category have pm
                if not opt_pm in self.result["pm"].keys():
                    del self.result["pm"][opt_pm]
                    break
                for dlv in run['dlv_production']:
                    conc_day = "day_" + str(dlv['days'])
                    if not conc_day in self.delivery_days_available.keys():
                        continue
                    if not conc_day in self.result["pm"][opt_pm].keys():
                        self.result["pm"][opt_pm][conc_day] = []
                    self.result["pm"][opt_pm][conc_day].append({
                        "start_cost": option["start_cost"],
                        "ppp": run['value'],
                        "add_price": dlv['value'],
                    })
                # return option_run
