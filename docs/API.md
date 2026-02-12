# 📘 Referência de API e Código

Este documento serve como referência rápida para padrões de código e APIs utilizadas nos tutoriais.

---

## 📚 Índice

- [Unity/C#](#unityc)
- [Godot/GDScript](#godotgdscript)
- [Pygame/Python](#pygamepython)
- [Phaser/JavaScript](#phaserjavascript)

---

## 🎮 Unity/C#

### Estrutura Básica de um Script

```csharp
using UnityEngine;

public class PlayerController : MonoBehaviour
{
    // Variáveis serializadas (aparecem no Inspector)
    [SerializeField] private float moveSpeed = 5f;
    [SerializeField] private float jumpForce = 10f;
    
    // Componentes
    private Rigidbody2D rb;
    private Animator animator;
    
    // Unity Lifecycle Methods
    private void Awake()
    {
        // Chamado quando o objeto é instanciado
        rb = GetComponent<Rigidbody2D>();
        animator = GetComponent<Animator>();
    }
    
    private void Start()
    {
        // Chamado antes do primeiro frame
    }
    
    private void Update()
    {
        // Chamado a cada frame
        HandleInput();
    }
    
    private void FixedUpdate()
    {
        // Chamado em intervalos fixos (física)
        HandleMovement();
    }
    
    private void HandleInput()
    {
        float horizontal = Input.GetAxisRaw("Horizontal");
        // Processar input
    }
    
    private void HandleMovement()
    {
        // Aplicar movimento
    }
}
```

## Padrões Comuns
### Singleton
```csharp
public class GameManager : MonoBehaviour
{
    public static GameManager Instance { get; private set; }
    
    private void Awake()
    {
        if (Instance != null && Instance != this)
        {
            Destroy(gameObject);
            return;
        }
        Instance = this;
        DontDestroyOnLoad(gameObject);
    }
}
```

### Object Pooling
```csharp
public class ObjectPool : MonoBehaviour
{
    [SerializeField] private GameObject prefab;
    [SerializeField] private int poolSize = 10;
    
    private Queue<GameObject> pool = new Queue<GameObject>();
    
    private void Start()
    {
        for (int i = 0; i < poolSize; i++)
        {
            GameObject obj = Instantiate(prefab);
            obj.SetActive(false);
            pool.Enqueue(obj);
        }
    }
    
    public GameObject Get()
    {
        if (pool.Count > 0)
        {
            GameObject obj = pool.Dequeue();
            obj.SetActive(true);
            return obj;
        }
        return Instantiate(prefab);
    }
    
    public void Return(GameObject obj)
    {
        obj.SetActive(false);
        pool.Enqueue(obj);
    }
}
```

## 🤖 Godot/GDScript
### Estrutura Básica de um Script
```gdscript
extends CharacterBody2D

# Constantes
const SPEED := 200.0
const JUMP_VELOCITY := -400.0

# Variáveis exportadas (aparecem no Inspector)
@export var max_health: int = 100

# Variáveis privadas
var _current_health: int
var _is_jumping: bool = false

# Referências a nós
@onready var sprite: Sprite2D = $Sprite2D
@onready var animation_player: AnimationPlayer = $AnimationPlayer

# Chamado quando o nó entra na árvore
func _ready() -> void:
    _current_health = max_health

# Chamado a cada frame
func _process(delta: float) -> void:
    _handle_animation()

# Chamado em intervalos fixos (física)
func _physics_process(delta: float) -> void:
    _handle_movement(delta)
    move_and_slide()

# Funções privadas
func _handle_movement(delta: float) -> void:
    # Gravidade
    if not is_on_floor():
        velocity.y += get_gravity().y * delta
    
    # Pulo
    if Input.is_action_just_pressed("jump") and is_on_floor():
        velocity.y = JUMP_VELOCITY
    
    # Movimento horizontal
    var direction := Input.get_axis("move_left", "move_right")
    if direction:
        velocity.x = direction * SPEED
    else:
        velocity.x = move_toward(velocity.x, 0, SPEED)

func _handle_animation() -> void:
    if velocity.x > 0:
        sprite.flip_h = false
    elif velocity.x < 0:
        sprite.flip_h = true
```

## Padrões Comuns
### Autoload (Singleton)
```gdscript
# Global.gd - Adicionar em Project Settings > Autoload
extends Node

var score: int = 0
var high_score: int = 0

signal score_changed(new_score)

func add_score(points: int) -> void:
    score += points
    score_changed.emit(score)
    if score > high_score:
        high_score = score
```

### State Machine
```gdscript
extends Node

enum State { IDLE, WALKING, JUMPING, FALLING }

var current_state: State = State.IDLE

func change_state(new_state: State) -> void:
    exit_state(current_state)
    current_state = new_state
    enter_state(new_state)

func enter_state(state: State) -> void:
    match state:
        State.IDLE:
            print("Entering IDLE")
        State.WALKING:
            print("Entering WALKING")
        # ...

func exit_state(state: State) -> void:
    match state:
        State.IDLE:
            print("Exiting IDLE")
        # ...
```

## 🐍 Pygame/Python
### Estrutura Básica de um Jogo
```python
import pygame
import sys

# Inicialização
pygame.init()

# Constantes
SCREEN_WIDTH = 800
SCREEN_HEIGHT = 600
FPS = 60

# Cores
WHITE = (255, 255, 255)
BLACK = (0, 0, 0)

# Setup
screen = pygame.display.set_mode((SCREEN_WIDTH, SCREEN_HEIGHT))
pygame.display.set_caption("Meu Jogo")
clock = pygame.time.Clock()


class Player(pygame.sprite.Sprite):
    def __init__(self, x: int, y: int):
        super().__init__()
        self.image = pygame.Surface((50, 50))
        self.image.fill((255, 0, 0))
        self.rect = self.image.get_rect()
        self.rect.x = x
        self.rect.y = y
        self.speed = 5
    
    def update(self):
        keys = pygame.key.get_pressed()
        if keys[pygame.K_LEFT]:
            self.rect.x -= self.speed
        if keys[pygame.K_RIGHT]:
            self.rect.x += self.speed
        if keys[pygame.K_UP]:
            self.rect.y -= self.speed
        if keys[pygame.K_DOWN]:
            self.rect.y += self.speed
        
        # Limites da tela
        self.rect.clamp_ip(screen.get_rect())


class Game:
    def __init__(self):
        self.running = True
        self.all_sprites = pygame.sprite.Group()
        self.player = Player(SCREEN_WIDTH // 2, SCREEN_HEIGHT // 2)
        self.all_sprites.add(self.player)
    
    def handle_events(self):
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                self.running = False
            if event.type == pygame.KEYDOWN:
                if event.key == pygame.K_ESCAPE:
                    self.running = False
    
    def update(self):
        self.all_sprites.update()
    
    def draw(self):
        screen.fill(BLACK)
        self.all_sprites.draw(screen)
        pygame.display.flip()
    
    def run(self):
        while self.running:
            self.handle_events()
            self.update()
            self.draw()
            clock.tick(FPS)
        
        pygame.quit()
        sys.exit()


if __name__ == "__main__":
    game = Game()
    game.run()
```

## Padrões Comuns
### Classe de Cena
```python
class Scene:
    def __init__(self):
        self.next_scene = None
    
    def handle_events(self, events):
        raise NotImplementedError
    
    def update(self, dt):
        raise NotImplementedError
    
    def draw(self, screen):
        raise NotImplementedError


class MenuScene(Scene):
    def __init__(self):
        super().__init__()
        self.font = pygame.font.Font(None, 74)
    
    def handle_events(self, events):
        for event in events:
            if event.type == pygame.KEYDOWN:
                if event.key == pygame.K_RETURN:
                    self.next_scene = GameScene()
    
    def update(self, dt):
        pass
    
    def draw(self, screen):
        screen.fill(BLACK)
        text = self.font.render("Press ENTER to Start", True, WHITE)
        screen.blit(text, (100, 300))
```

## 🌐 Phaser/JavaScript
### Estrutura Básica de um Jogo
```javascript
// config.js
const config = {
    type: Phaser.AUTO,
    width: 800,
    height: 600,
    physics: {
        default: 'arcade',
        arcade: {
            gravity: { y: 300 },
            debug: false
        }
    },
    scene: [BootScene, MenuScene, GameScene]
};

const game = new Phaser.Game(config);
```

```javascript
// scenes/GameScene.js
class GameScene extends Phaser.Scene {
    constructor() {
        super({ key: 'GameScene' });
    }
    
    preload() {
        this.load.image('player', 'assets/player.png');
        this.load.image('platform', 'assets/platform.png');
    }
    
    create() {
        // Plataformas
        this.platforms = this.physics.add.staticGroup();
        this.platforms.create(400, 568, 'platform').setScale(2).refreshBody();
        
        // Player
        this.player = this.physics.add.sprite(100, 450, 'player');
        this.player.setBounce(0.2);
        this.player.setCollideWorldBounds(true);
        
        // Colisão
        this.physics.add.collider(this.player, this.platforms);
        
        // Input
        this.cursors = this.input.keyboard.createCursorKeys();
    }
    
    update() {
        if (this.cursors.left.isDown) {
            this.player.setVelocityX(-160);
        } else if (this.cursors.right.isDown) {
            this.player.setVelocityX(160);
        } else {
            this.player.setVelocityX(0);
        }
        
        if (this.cursors.up.isDown && this.player.body.touching.down) {
            this.player.setVelocityY(-330);
        }
    }
}
```

## 📚 Referências Úteis
### Documentações Oficiais
| Engine |	Link |
| ------ | ---- |
| Unity |	docs.unity3d.com |
| Godot |	docs.godotengine.org |
| Pygame | pygame.org/docs |
| Phaser |	phaser.io/docs |
