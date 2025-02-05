def generate_card_html(num_cards):
    front_html = ""
    back_html = ""
    for i in range(1, num_cards + 1):
        front_class = chr(65 + (i - 1) // 676) + chr(65 + ((i - 1) % 676) // 26) + chr(65 + (i - 1) % 26)
        back_class = chr(65 + (i - 1) // 676) + chr(65 + ((i - 1) % 676) // 26) + chr(65 + (i - 1) % 26) + "B"
        front_html += f'  <div class="card {front_class}"></div>\n'
        back_html += f'  <div class="card {back_class}"></div>\n'
    return front_html, back_html

front_html_output, back_html_output = generate_card_html(104)

with open("card_front_grid.html", "w") as f:
    f.write(front_html_output)

with open("card_back_grid.html", "w") as f:
    f.write(back_html_output)

print("Card HTML generated to card_front_grid.html and card_back_grid.html")