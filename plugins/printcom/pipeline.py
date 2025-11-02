class Pipeline:
    def __init__(self, steps):
        self.steps = steps

    def execute(self, data):
        for step in self.steps:
            data = step.process(data)
        return data
