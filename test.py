import json, pprint
pp = pprint.PrettyPrinter(indent=1)

f = open('test.txt', "r")
for line in f:
	pp.pprint(json.loads(line))
	