"""Instantiate static TTFs from the variable Geist fonts so text can be outlined in SVG."""
from fontTools.ttLib import TTFont
from fontTools.varLib import instancer
SRC = {"Geist": "design/fonts/geist-latin-wght-normal.woff2", "GeistMono": "design/fonts/geist-mono-latin-wght-normal.woff2"}
for name, weights in {"Geist": [400, 500, 600], "GeistMono": [400, 500]}.items():
    for w in weights:
        f = TTFont(SRC[name])
        inst = instancer.instantiateVariableFont(f, {"wght": w})
        inst.flavor = None
        inst.save(f"brand/fonts/{name}-{w}.ttf")
        print(name, w)
