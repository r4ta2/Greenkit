<?php 
session_start();

	include("connection.php");
	include("functions.php");

	$user_data = check_login($con);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizQuest</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            text-align: center;
        }

        .game-container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        #answers-container button {
            margin: 10px;
            padding: 10px;
            background-color: #e7e7e7;
            border: none;
            cursor: pointer;
        }

        #answers-container button:hover {
            background-color: #ddd;
        }

        #market-container, #blook-tab, #question-tab, #sell-container, #chat-tab, #leaderboard-tab {
            display: none;
            margin-top: 20px;
        }

        .pack {
            margin: 10px;
            padding: 10px;
            background-color: #e7e7e7;
            cursor: pointer;
            border-radius: 5px;
        }

        .pack:hover {
            background-color: #ddd;
        }

        #blook-tab ul, #question-tab ul, #sell-list, #chat-log, #leaderboard-list {
            list-style-type: none;
            padding: 0;
        }

        #blook-tab li, #question-tab li, #sell-list li, #chat-log li, #leaderboard-list li {
            background-color: #e7e7e7;
            margin: 5px;
            padding: 10px;
        }

        .tabs-container button.active {
            background-color: #2196F3;
            color: white;
        }

        .tabs-container {
            margin-top: 20px;
        }

        .tabs-container button {
            margin: 5px;
        }

        #chat-input {
            padding: 10px;
            width: 70%;
            margin-top: 10px;
        }

        #send-chat-btn {
            padding: 10px;
            background-color: #2196F3;
            color: white;
            border: none;
            cursor: pointer;
        }

        #send-chat-btn:hover {
            background-color: #0b7dda;
        }

        #user-info {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <h1>QuizQuest</h1>
        <div id="user-info">Welcome, <span id="username">Guest</span>!</div>
        <div id="question-container"></div>
        <div id="answers-container"></div>
        <div id="score-container">
            Tokens: <span id="score">0</span>
        </div>
        <button id="next-btn" style="display:none">Next Question</button>

        <div class="tabs-container">
            <button onclick="toggleMarket(this)">Visit Market</button>
            <button onclick="showBlookTab(this)">View Your Blooks</button>
            <button onclick="showSellTab(this)">Sell Blooks</button>
            <button onclick="showQuestionTab(this)">Review Questions</button>
            <button onclick="showChatTab(this)">Live Chat</button>
            <button onclick="showLeaderboardTab(this)">Leaderboard</button>
            <button onclick="startNewGame()">New Game</button>
        </div>

        <div id="market-container">
            <h2>Welcome to the Market</h2>
            <p>Buy a pack to unlock QuizBlooks!</p>
            <div class="pack" onclick="buyPack('basic')">
                Buy Basic Pack - 10 Tokens
            </div>
            <div class="pack" onclick="buyPack('premium')">
                Buy Premium Pack - 25 Tokens
            </div>
            <!-- Added Dino Pack element -->
            <div class="pack" onclick="buyDinoPack()">
                Buy Dino Pack - 10 Tokens
            </div>
        </div>

        <div id="blook-tab">
            <h2>Your Blooks</h2>
            <ul id="blook-list"></ul>
        </div>

        <div id="sell-container">
            <h2>Sell Your Blooks</h2>
            <ul id="sell-list"></ul>
        </div>

        <div id="question-tab">
            <h2>Question Review</h2>
            <ul id="question-list"></ul>
        </div>

        <div id="chat-tab">
            <h2>Live Chat</h2>
            <ul id="chat-log"></ul>
            <input type="text" id="chat-input" placeholder="Type your message...">
            <button id="send-chat-btn">Send</button>
        </div>

        <div id="leaderboard-tab">
            <h2>Leaderboard</h2>
            <ul id="leaderboard-list"></ul>
        </div>
    </div>

    <script>
    const questions = [
        {
            question: "What is the capital of France?",
            answers: ["Berlin", "Madrid", "Paris", "Rome"],
            correctAnswer: "Paris"
        },
        {
            question: "Which planet is known as the Red Planet?",
            answers: ["Earth", "Mars", "Jupiter", "Saturn"],
            correctAnswer: "Mars"
        },
        {
            question: "What is the largest ocean on Earth?",
            answers: ["Atlantic", "Indian", "Arctic", "Pacific"],
            correctAnswer: "Pacific"
        },
        {
            question: "What is the smallest prime number?",
            answers: ["0", "1", "2", "3"],
            correctAnswer: "2"
        },
        {
            question: "Which language is used to style web pages?",
            answers: ["HTML", "JQuery", "CSS", "XML"],
            correctAnswer: "CSS"
        },
        {
            question: "What does CPU stand for?",
            answers: ["Central Process Unit", "Computer Personal Unit", "Central Processing Unit", "Control Panel Utility"],
            correctAnswer: "Central Processing Unit"
        },
        {
            question: "Who painted the Mona Lisa?",
            answers: ["Vincent Van Gogh", "Leonardo da Vinci", "Pablo Picasso", "Claude Monet"],
            correctAnswer: "Leonardo da Vinci"
        },
        {
            question: "What is the chemical symbol for water?",
            answers: ["HO", "H2O", "O2", "OH2"],
            correctAnswer: "H2O"
        },
        {
            question: "What is the square root of 64?",
            answers: ["6", "7", "8", "9"],
            correctAnswer: "8"
        },
        {
            question: "Which country is home to the kangaroo?",
            answers: ["New Zealand", "South Africa", "Australia", "India"],
            correctAnswer: "Australia"
        }
    ];

    let currentQuestionIndex = 0;
    let score = 0;
    let unlockedBlooks = [];
    let answeredQuestions = [];
    let leaderboard = [];
    let lastActiveTabButton = null;

    function loadQuestion() {
        const questionContainer = document.getElementById('question-container');
        const answersContainer = document.getElementById('answers-container');
        const nextBtn = document.getElementById('next-btn');
        const currentQuestion = questions[currentQuestionIndex];

        questionContainer.textContent = currentQuestion.question;
        answersContainer.innerHTML = '';

        currentQuestion.answers.forEach(answer => {
            const answerButton = document.createElement('button');
            answerButton.textContent = answer;
            answerButton.onclick = function() {
                checkAnswer(answer, answerButton);
            };
            answersContainer.appendChild(answerButton);
        });

        nextBtn.style.display = "none";
    }

    function checkAnswer(selectedAnswer, selectedButton) {
        const currentQuestion = questions[currentQuestionIndex];
        const nextBtn = document.getElementById('next-btn');
        const answerButtons = document.querySelectorAll('#answers-container button');

        answeredQuestions.push({
            question: currentQuestion.question,
            selectedAnswer: selectedAnswer,
            correctAnswer: currentQuestion.correctAnswer
        });

        answerButtons.forEach(button => {
            button.disabled = true;
            if (button.textContent === currentQuestion.correctAnswer) {
                button.style.backgroundColor = '#4CAF50';
                button.style.color = 'white';
            } else if (button.textContent === selectedAnswer) {
                button.style.backgroundColor = '#f44336';
                button.style.color = 'white';
            }
        });

        if (selectedAnswer === currentQuestion.correctAnswer) {
            score++;
            document.getElementById('score').textContent = score;
        }

        nextBtn.style.display = "inline-block";
        nextBtn.onclick = function() {
            currentQuestionIndex++;
            if (currentQuestionIndex < questions.length) {
                loadQuestion();
            } else {
                showFinalScore();
            }
        };
    }

    function showFinalScore() {
        document.getElementById('question-container').textContent = "Game Over!";
        document.getElementById('answers-container').innerHTML = `Your final tokens count is: ${score}`;
        document.getElementById('next-btn').style.display = "none";
        leaderboard.push(score);
    }

    function startNewGame() {
        currentQuestionIndex = 0;
        unlockedBlooks = [];
        answeredQuestions = [];
        document.getElementById('score').textContent = score;
        loadQuestion();
    }

    function buyPack(packType) {
        if (packType === 'basic' && score >= 10) {
            score -= 10;
            unlockedBlooks.push('Basic Blook');
            alert("You unlocked a Basic Blook!");
        } else if (packType === 'premium' && score >= 25) {
            score -= 25;
            unlockedBlooks.push('Premium Blook');
            alert("You unlocked a Premium Blook!");
        } else {
            alert("Not enough tokens!");
        }
        document.getElementById('score').textContent = score;
    }

    function setActiveTab(button) {
        if (lastActiveTabButton) lastActiveTabButton.classList.remove('active');
        if (button) button.classList.add('active');
        lastActiveTabButton = button;

        document.getElementById('market-container').style.display = 'none';
        document.getElementById('blook-tab').style.display = 'none';
        document.getElementById('sell-container').style.display = 'none';
        document.getElementById('question-tab').style.display = 'none';
        document.getElementById('chat-tab').style.display = 'none';
        document.getElementById('leaderboard-tab').style.display = 'none';
    }

    function toggleMarket(button) {
        const container = document.getElementById('market-container');
        if (container.style.display === 'none') {
            setActiveTab(button);
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
            button.classList.remove('active');
        }
    }

    function showBlookTab(button) {
        setActiveTab(button);
        const blookTab = document.getElementById('blook-tab');
        const blookList = document.getElementById('blook-list');
        blookList.innerHTML = '';
        unlockedBlooks.forEach(blook => {
            const listItem = document.createElement('li');

            const img = document.createElement('img');
            img.src = getBlookImage(blook);
            img.alt = blook;
            img.style.height = '50px';
            img.style.marginRight = '10px';
            img.style.verticalAlign = 'middle';

            listItem.appendChild(img);
            listItem.appendChild(document.createTextNode(blook));
            blookList.appendChild(listItem);
        });
        blookTab.style.display = 'block';
    }

    function getBlookImage(blook) {
        const imageMap = {
            "Trex (Legendary)": "https://i.imgur.com/e1hLQ2j.png",
            "Triceratops (Epic)": "https://i.imgur.com/2Y2ZQ1j.png",
            "Velociraptor": "https://i.imgur.com/WH9OmtD.png",
            "Stegosaurus": "https://i.imgur.com/fM0nPqA.png",
            "Brachiosaurus": "https://i.imgur.com/hZMP8dU.png"
        };
        return imageMap[blook] || "https://i.imgur.com/Default.png";
    }

    function showSellTab(button) {
        setActiveTab(button);
        const sellContainer = document.getElementById('sell-container');
        const sellList = document.getElementById('sell-list');
        sellList.innerHTML = '';
        unlockedBlooks.forEach((blook, index) => {
            let sellPrice = 5;
            if (blook.includes("Trex (Legendary)")) {
                sellPrice = 200;
            }
            else if (blook.includes("Triceratops (Epic)")) {
                sellPrice = 150;
            }
            const listItem = document.createElement('li');
            listItem.textContent = `${blook} - Sell for ${sellPrice} Tokens`;
            listItem.onclick = function() {
                unlockedBlooks.splice(index, 1);
                score += sellPrice;
                document.getElementById('score').textContent = score;
                showSellTab(button);
            };
            sellList.appendChild(listItem);
        });
        sellContainer.style.display = 'block';
    }

    function showQuestionTab(button) {
        setActiveTab(button);
        const questionTab = document.getElementById('question-tab');
        const questionList = document.getElementById('question-list');
        questionList.innerHTML = '';
        answeredQuestions.forEach(q => {
            const listItem = document.createElement('li');
            listItem.innerHTML = `
                <strong>Question:</strong> ${q.question} <br>
                <strong>Your Answer:</strong> ${q.selectedAnswer} <br>
                <strong>Correct Answer:</strong> ${q.correctAnswer}`;
            questionList.appendChild(listItem);
        });
        questionTab.style.display = 'block';
    }

    function showChatTab(button) {
        setActiveTab(button);
        document.getElementById('chat-tab').style.display = 'block';
    }

    function showLeaderboardTab(button) {
        setActiveTab(button);
        const leaderboardTab = document.getElementById('leaderboard-tab');
        const leaderboardList = document.getElementById('leaderboard-list');
        leaderboardList.innerHTML = '';
        leaderboard.sort((a, b) => b - a).forEach((score, index) => {
            const li = document.createElement('li');
            li.textContent = `#${index + 1}: ${score} Tokens`;
            leaderboardList.appendChild(li);
        });
        leaderboardTab.style.display = 'block';
    }

    document.getElementById('send-chat-btn').onclick = function() {
        const chatInput = document.getElementById('chat-input');
        const message = chatInput.value.trim();
        if (message) {
            const chatLog = document.getElementById('chat-log');
            const li = document.createElement('li');
            li.textContent = message;
            chatLog.appendChild(li);
            chatInput.value = '';
        }
    };

    function setUsername(name) {
        document.getElementById('username').textContent = name;
    }

    setUsername('Player1');
    loadQuestion();

    function buyDinoPack() {
        const cost = 10;
        if (score >= cost) {
            score -= cost;
            let selectedDino;
            const chance = Math.random();
            if (chance < 0.05) {
                selectedDino = "Trex (Legendary)";
            } else if (chance < 0.15) {
                selectedDino = "Triceratops (Epic)";
            } else {
                const others = ['Velociraptor', 'Stegosaurus', 'Brachiosaurus'];
                const randomIndex = Math.floor(Math.random() * others.length);
                selectedDino = others[randomIndex];
            }
            unlockedBlooks.push(selectedDino);
            alert("You unlocked a " + selectedDino + "!");
        } else {
            alert("Not enough tokens!");
        }
        document.getElementById('score').textContent = score;
    }

    function instantOpenDinoPack(button) {
        buyDinoPack();
        showBlookTab(button);
    }
</script>
</body>
</html>
	<a href="logout.php">Logout</a>
	<h1>This is the index page</h1>

	<br>
	Hello, <?php echo $user_data['user_name']; ?>
</body>
</html>