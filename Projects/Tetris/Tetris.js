const canvas = document.getElementById('tetris');
const context = canvas.getContext('2d');
context.scale(20, 20);

const arena = createMatrix(12, 20);
const player = {
  pos: { x: 0, y: 0 },
  matrix: null,
  score: 0
};

let dropCounter = 0;
let dropInterval = 1000;
let lastTime = 0;
let animationFrameId;
let paused = false;

function createMatrix(w, h) {
  const matrix = [];
  while (h--) matrix.push(new Array(w).fill(0));
  return matrix;
}

function createPiece(type) {
  const pieces = {
    T: [[0, 1, 0], [1, 1, 1], [0, 0, 0]],
    O: [[1, 1], [1, 1]],
    L: [[0, 0, 1], [1, 1, 1], [0, 0, 0]],
    J: [[1, 0, 0], [1, 1, 1], [0, 0, 0]],
    I: [[0, 0, 0, 0], [1, 1, 1, 1], [0, 0, 0, 0], [0, 0, 0, 0]],
    S: [[0, 1, 1], [1, 1, 0], [0, 0, 0]],
    Z: [[1, 1, 0], [0, 1, 1], [0, 0, 0]]
  };
  return pieces[type];
}

function drawMatrix(matrix, offset) {
  matrix.forEach((row, y) => {
    row.forEach((value, x) => {
      if (value !== 0) {
        context.fillStyle = 'red';
        context.fillRect(x + offset.x, y + offset.y, 1, 1);
        context.strokeStyle = 'white';
        context.lineWidth = 0.05;
        context.strokeRect(x + offset.x, y + offset.y, 1, 1);
      }
    });
  });
}

function drawGrid() {
  context.strokeStyle = '#333';
  context.lineWidth = 0.05;
  for (let x = 0; x < canvas.width / 20; x++) {
    context.beginPath();
    context.moveTo(x, 0);
    context.lineTo(x, canvas.height / 20);
    context.stroke();
  }
  for (let y = 0; y < canvas.height / 20; y++) {
    context.beginPath();
    context.moveTo(0, y);
    context.lineTo(canvas.width / 20, y);
    context.stroke();
  }
}

function draw() {
  context.fillStyle = '#000';
  context.fillRect(0, 0, canvas.width, canvas.height);
  drawGrid();
  drawMatrix(arena, { x: 0, y: 0 });
  drawMatrix(player.matrix, player.pos);
}

function collide(arena, player) {
  const [m, o] = [player.matrix, player.pos];
  for (let y = 0; y < m.length; ++y) {
    for (let x = 0; x < m[y].length; ++x) {
      if (m[y][x] !== 0 &&
          (arena[y + o.y] && arena[y + o.y][x + o.x]) !== 0) {
        return true;
      }
    }
  }
  return false;
}

function merge(arena, player) {
  player.matrix.forEach((row, y) => {
    row.forEach((value, x) => {
      if (value !== 0) {
        arena[y + player.pos.y][x + player.pos.x] = value;
      }
    });
  });
}

function arenaSweep() {
  let rowCount = 1;
  for (let y = arena.length - 1; y >= 0; y--) {
    if (arena[y].every(value => value !== 0)) {
      arena.splice(y, 1);
      arena.unshift(new Array(arena[0].length).fill(0));
      player.score += rowCount * 10;
      rowCount *= 2;
    }
  }
}

function playerDrop() {
  player.pos.y++;
  if (collide(arena, player)) {
    player.pos.y--;
    merge(arena, player);
    playerReset();
    arenaSweep();
    updateScore();
  }
  dropCounter = 0;
}

function playerMove(dir) {
  player.pos.x += dir;
  if (collide(arena, player)) player.pos.x -= dir;
}

function playerRotate(dir) {
  const m = player.matrix;
  for (let y = 0; y < m.length; ++y) {
    for (let x = 0; x < y; ++x) {
      [m[x][y], m[y][x]] = [m[y][x], m[x][y]];
    }
  }
  dir > 0 ? m.forEach(row => row.reverse()) : m.reverse();
  if (collide(arena, player)) {
    dir > 0 ? m.forEach(row => row.reverse()) : m.reverse();
  }
}

function playerReset() {
  const pieces = 'TJLOSZI';
  player.matrix = createPiece(pieces[Math.floor(Math.random() * pieces.length)]);
  player.pos.y = 0;
  player.pos.x = Math.floor(arena[0].length / 2) - Math.floor(player.matrix[0].length / 2);
  if (collide(arena, player)) showGameOver();
}

function updateScore() {
  document.getElementById('score').innerText = player.score;
}

function showGameOver() {
  document.getElementById('gameOverPopup').style.display = 'block';
  cancelAnimationFrame(animationFrameId);
}

function restartGame() {
  arena.forEach(row => row.fill(0));
  player.score = 0;
  updateScore();
  document.getElementById('gameOverPopup').style.display = 'none';
  playerReset();
  update();
}

function update(time = 0) {
  if (paused) return;
  const deltaTime = time - lastTime;
  lastTime = time;
  dropCounter += deltaTime;
  if (dropCounter > dropInterval) playerDrop();
  draw();
  animationFrameId = requestAnimationFrame(update);
}

document.addEventListener('keydown', event => {
  if (paused && event.key !== 'Enter') return;
  switch (event.key) {
    case 'ArrowLeft': playerMove(-1); break;
    case 'ArrowRight': playerMove(1); break;
    case 'ArrowDown': playerDrop(); break;
    case 'ArrowUp': playerRotate(1); break;
    case ' ': // Hard drop
      while (!collide(arena, player)) player.pos.y++;
      player.pos.y--;
      merge(arena, player);
      playerReset();
      arenaSweep();
      updateScore();
      dropCounter = 0;
      break;
    case 'Enter':
      paused = !paused;
      console.log(paused ? 'Game paused' : 'Game resumed');
      if (!paused) update();
      break;
  }
});

function submitName() {
  const name = document.getElementById("playerName").value.trim();
  if (name === "") {
    alert("Please enter your name.");
    return;
  }
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "Tetris.php";

  const input = document.createElement("input");
  input.type = "hidden";
  input.name = "playerName";
  input.value = name;

  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
}

playerReset();
update();