from flask import Flask, render_template, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Sample data
games_data = [
    {
        "id": 1,
        "title": "The Witcher 3: Wild Hunt",
        "genre": "RPG, Action, Fantasy",
        "description": "An open-world RPG game with rich storytelling, monster hunting, and magic.",
        "platform": "PC, PS4, Xbox, Switch",
        "year": 2015
    },
    {
        "id": 2,
        "title": "Minecraft",
        "genre": "Sandbox, Survival, Creative",
        "description": "A sandbox game where you can build, explore, and survive.",
        "platform": "PC, PS4, Xbox, Switch, Mobile",
        "year": 2011
    }
]

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/api/games')
def get_games():
    return jsonify(games_data)

@app.route('/api/test')
def test():
    return jsonify({"status": "ok", "message": "Server is running!"})

if __name__ == '__main__':
    print("Starting simple Flask server...")
    print("Open: http://localhost:5000")
    app.run(debug=True, host='0.0.0.0', port=5000)
