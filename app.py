from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np
import json

app = Flask(__name__)
CORS(app)

# Load SBERT model
print("Loading SBERT model...")
model = SentenceTransformer('paraphrase-MiniLM-L6-v2')
print("Model loaded successfully!")

# Game database
games_data = [
    {
        "id": 1,
        "title": "The Witcher 3: Wild Hunt",
        "genre": "RPG, Action, Fantasy",
        "description": "An open-world RPG game with rich storytelling, monster hunting, and magic. Follow Geralt's journey to find Ciri.",
        "platform": "PC, PS4, Xbox, Switch",
        "year": 2015
    },
    {
        "id": 2,
        "title": "Dark Souls 3",
        "genre": "RPG, Action, Dark Fantasy",
        "description": "A challenging action RPG with difficult combat, dark atmosphere, and interconnected world design.",
        "platform": "PC, PS4, Xbox",
        "year": 2016
    },
    {
        "id": 3,
        "title": "Stardew Valley",
        "genre": "Simulation, Farming, RPG",
        "description": "A farming simulation game where you build your farm, interact with villagers, and explore caves.",
        "platform": "PC, PS4, Xbox, Switch, Mobile",
        "year": 2016
    },
    {
        "id": 4,
        "title": "Minecraft",
        "genre": "Sandbox, Survival, Creative",
        "description": "A sandbox game where you can build, explore, and survive in a blocky procedurally generated world.",
        "platform": "PC, PS4, Xbox, Switch, Mobile",
        "year": 2011
    },
    {
        "id": 5,
        "title": "Elden Ring",
        "genre": "RPG, Action, Dark Fantasy",
        "description": "A massive open-world action RPG with challenging combat, exploration, and FromSoftware's signature difficulty.",
        "platform": "PC, PS5, Xbox Series X",
        "year": 2022
    },
    {
        "id": 6,
        "title": "Red Dead Redemption 2",
        "genre": "Action, Adventure, Western",
        "description": "An open-world western adventure with stunning graphics, realistic gameplay, and an emotional story.",
        "platform": "PC, PS4, Xbox",
        "year": 2018
    },
    {
        "id": 7,
        "title": "God of War",
        "genre": "Action, Adventure, Mythology",
        "description": "Follow Kratos and his son Atreus in Norse mythology with intense combat and emotional storytelling.",
        "platform": "PC, PS4, PS5",
        "year": 2018
    },
    {
        "id": 8,
        "title": "Animal Crossing: New Horizons",
        "genre": "Simulation, Life Simulation, Casual",
        "description": "A relaxing life simulation game where you build your island paradise and interact with cute animal villagers.",
        "platform": "Switch",
        "year": 2020
    },
    {
        "id": 9,
        "title": "Terraria",
        "genre": "Sandbox, Survival, Adventure",
        "description": "A 2D sandbox game with exploration, crafting, building, and combat against various enemies and bosses.",
        "platform": "PC, PS4, Xbox, Switch, Mobile",
        "year": 2011
    },
    {
        "id": 10,
        "title": "Horizon Zero Dawn",
        "genre": "RPG, Action, Post-Apocalyptic",
        "description": "Hunt robotic dinosaurs in a beautiful post-apocalyptic world with engaging combat and story.",
        "platform": "PC, PS4, PS5",
        "year": 2017
    },
    {
        "id": 11,
        "title": "Cyberpunk 2077",
        "genre": "RPG, Action, Sci-Fi",
        "description": "An open-world RPG set in a futuristic cyberpunk city with deep customization and branching storylines.",
        "platform": "PC, PS5, Xbox Series X",
        "year": 2020
    },
    {
        "id": 12,
        "title": "Portal 2",
        "genre": "Puzzle, First-Person, Sci-Fi",
        "description": "A mind-bending puzzle game with portal mechanics, humor, and creative level design.",
        "platform": "PC, PS3, Xbox 360",
        "year": 2011
    }
]

# Precompute embeddings for all games
print("Computing game embeddings...")
game_texts = []
for game in games_data:
    # Combine title, genre, and description for better semantic understanding
    text = f"{game['title']}. Genre: {game['genre']}. {game['description']}"
    game_texts.append(text)

game_embeddings = model.encode(game_texts)
print(f"Computed embeddings for {len(games_data)} games")

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/api/games', methods=['GET'])
def get_games():
    """Get all games"""
    return jsonify(games_data)

@app.route('/api/recommend', methods=['POST'])
def recommend():
    """Get game recommendations based on user query"""
    try:
        data = request.json
        query = data.get('query', '')
        game_id = data.get('game_id', None)
        top_n = data.get('top_n', 5)
        
        if not query and not game_id:
            return jsonify({"error": "Please provide either a query or game_id"}), 400
        
        if game_id:
            # Recommend based on a specific game
            if game_id < 1 or game_id > len(games_data):
                return jsonify({"error": "Invalid game_id"}), 400
            
            query_embedding = game_embeddings[game_id - 1].reshape(1, -1)
        else:
            # Recommend based on text query
            query_embedding = model.encode([query])
        
        # Calculate cosine similarity
        similarities = cosine_similarity(query_embedding, game_embeddings)[0]
        
        # Get top N recommendations
        top_indices = np.argsort(similarities)[::-1]
        
        # If recommending based on a game, exclude the game itself
        if game_id:
            top_indices = [idx for idx in top_indices if idx != game_id - 1]
        
        top_indices = top_indices[:top_n]
        
        # Prepare recommendations
        recommendations = []
        for idx in top_indices:
            game = games_data[idx].copy()
            game['similarity_score'] = float(similarities[idx])
            recommendations.append(game)
        
        return jsonify({
            "query": query if query else f"Similar to {games_data[game_id-1]['title']}",
            "recommendations": recommendations
        })
    
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/api/search', methods=['POST'])
def search():
    """Search games by text similarity"""
    try:
        data = request.json
        query = data.get('query', '')
        
        if not query:
            return jsonify({"error": "Please provide a search query"}), 400
        
        # Encode the query
        query_embedding = model.encode([query])
        
        # Calculate cosine similarity with all games
        similarities = cosine_similarity(query_embedding, game_embeddings)[0]
        
        # Add similarity scores to games
        results = []
        for idx, game in enumerate(games_data):
            game_copy = game.copy()
            game_copy['similarity_score'] = float(similarities[idx])
            results.append(game_copy)
        
        # Sort by similarity
        results.sort(key=lambda x: x['similarity_score'], reverse=True)
        
        return jsonify({
            "query": query,
            "results": results
        })
    
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
