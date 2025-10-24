def generate_card_css(num_cards, columns=11, rows=10):
    css = ""
    for i in range(1, num_cards + 1):
        front_class = chr(65 + (i - 1) // 676) + chr(65 + ((i - 1) % 676) // 26) + chr(65 + (i - 1) % 26)
        back_class = chr(65 + (i - 1) // 676) + chr(65 + ((i - 1) % 676) // 26) + chr(65 + (i - 1) % 26) + "B"

        col = (i - 1) % columns
        row = (i - 1) // columns

        css += f"/* card {i} */\n"
        css += f".{front_class} {{\n"
        css += f"  background-image: url(img/front.png);\n"
        css += f"  background-position: calc(-{col} * var(--size)) calc(-{row} * var(--size));\n"
        css += f"  width: var(--size);\n"
        css += f"  height: var(--size);\n"
        css += f"}}\n\n"

        css += f".{back_class} {{\n"
        css += f"  background-image: url(img/back.png);\n"
        css += f"  background-position: calc(-{col} * var(--size)) calc(-{row} * var(--size));\n"
        css += f"  width: var(--size);\n"
        css += f"  height: var(--size);\n"
        css += f"}}\n\n"
    return css

css_output = generate_card_css(104)
with open("card_styles.css", "w") as f:
    f.write(css_output)

print("CSS generated to card_styles.css")