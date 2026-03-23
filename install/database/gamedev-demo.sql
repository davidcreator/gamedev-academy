SET FOREIGN_KEY_CHECKS = 0;

INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `order_index`, `sort_order`, `is_active`, `status`, `course_count`) VALUES
(101, 'Phaser', 'phaser', 'Cursos de jogos 2D com Phaser.', 'phaser', '#4f46e5', 1, 1, 1, 'active', 1),
(102, 'Godot', 'godot', 'Cursos e prototipos com Godot 4.', 'godot', '#2563eb', 2, 2, 1, 'active', 1),
(103, 'Unity', 'unity', 'Formacao para jogos 2D e 3D com Unity.', 'unity', '#111827', 3, 3, 1, 'active', 2),
(104, 'Pixel Art', 'pixel-art', 'Arte, animacao e pipeline visual para games.', 'palette', '#f97316', 4, 4, 1, 'active', 2),
(105, 'Game Design', 'game-design', 'Sistemas, progressao e experiencia do jogador.', 'dice', '#d946ef', 5, 5, 1, 'active', 1),
(106, 'Monetizacao', 'monetizacao', 'Monetizacao, precificacao e retencao.', 'coins', '#16a34a', 6, 6, 1, 'active', 1),
(107, 'Arte 2D', 'arte-2d', 'Direcao de arte, moodboard e consistencia visual.', 'brush', '#ea580c', 7, 7, 1, 'active', 1),
(108, 'IA para Jogos', 'ia-para-jogos', 'Comportamentos, tomada de decisao e NPCs.', 'brain', '#0891b2', 8, 8, 1, 'active', 1),
(109, 'Prototipacao', 'prototipacao', 'Validacao rapida de hipoteses e gameplay.', 'rocket', '#7c3aed', 9, 9, 1, 'active', 1),
(110, 'Publicacao', 'publicacao', 'Go to market, loja e lancamento.', 'megaphone', '#dc2626', 10, 10, 1, 'active', 1),
(111, 'Masterclasses', 'masterclasses', 'Aulas especiais de curta duracao.', 'star', '#ca8a04', 11, 11, 1, 'active', 5),
(112, 'Plataforma', 'plataforma', 'Noticias e comunicados da plataforma.', 'news', '#0f766e', 12, 12, 1, 'active', 0);

INSERT IGNORE INTO `users` (
    `id`, `name`, `full_name`, `username`, `email`, `password`, `role`, `avatar`, `bio`,
    `specialization`, `total_points`, `xp_total`, `experience_points`, `coins`, `level`,
    `streak_days`, `last_activity`, `email_verified_at`, `last_login_at`, `is_active`, `status`
) VALUES
(1001, 'Demo Admin', 'Demo Admin', 'demoadmin', 'demoadmin@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'super_admin', 'https://i.pravatar.cc/300?u=demoadmin', 'Conta demo com acesso total ao painel.', 'Operacoes e administracao', 0, 0, 0, 0, 1, 3, '2026-03-23', '2026-03-01 09:00:00', '2026-03-23 08:00:00', 1, 'active'),
(1002, 'Demo Instrutor', 'Demo Instrutor', 'demoinstrutor', 'demoinstrutor@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'instructor', 'https://i.pravatar.cc/300?u=demoinstrutor', 'Instrutor demo com acesso restrito para criar e editar conteudo.', 'Phaser e publicacao de jogos', 0, 0, 0, 0, 1, 7, '2026-03-23', '2026-03-01 09:00:00', '2026-03-23 08:30:00', 1, 'active'),
(1003, 'Tiago Phaser', 'Tiago Phaser', 'tiagophaser', 'tiago@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'instructor', 'https://i.pravatar.cc/300?u=tiago', 'Instrutor focado em Godot e prototipacao.', 'Godot e prototipos', 0, 0, 0, 0, 1, 5, '2026-03-22', '2026-03-01 09:00:00', '2026-03-22 14:00:00', 1, 'active'),
(1004, 'Bruna Unity', 'Bruna Unity', 'brunaunity', 'bruna@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'instructor', 'https://i.pravatar.cc/300?u=bruna', 'Instrutora de Unity, camera e sistemas 2D.', 'Unity e IA para jogos', 0, 0, 0, 0, 1, 6, '2026-03-22', '2026-03-01 09:00:00', '2026-03-22 13:00:00', 1, 'active'),
(1005, 'Carol Art', 'Carol Art', 'carolart', 'carol@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'instructor', 'https://i.pravatar.cc/300?u=carol', 'Instrutora de pixel art e direcao de arte para jogos.', 'Pixel art e direcao de arte', 0, 0, 0, 0, 1, 8, '2026-03-22', '2026-03-01 09:00:00', '2026-03-22 16:00:00', 1, 'active'),
(1006, 'Diego Design', 'Diego Design', 'diegodesign', 'diego@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'instructor', 'https://i.pravatar.cc/300?u=diego', 'Instrutor de game design e monetizacao.', 'Game design e monetizacao', 0, 0, 0, 0, 1, 4, '2026-03-21', '2026-03-01 09:00:00', '2026-03-21 18:00:00', 1, 'active'),
(1101, 'Ana Lima', 'Ana Lima', 'anaaluna', 'ana.aluna@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=anaaluna', 'Aluna destaque no ranking demo.', NULL, 8200, 8200, 8200, 420, 9, 18, '2026-03-23', '2026-03-01 09:00:00', '2026-03-23 07:50:00', 1, 'active'),
(1102, 'Bruno Costa', 'Bruno Costa', 'brunoaluno', 'bruno.aluno@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=brunoaluno', 'Aluno com foco em cursos longos.', NULL, 7600, 7600, 7600, 395, 8, 14, '2026-03-23', '2026-03-01 09:00:00', '2026-03-22 22:10:00', 1, 'active'),
(1103, 'Camila Rocha', 'Camila Rocha', 'camilaaluna', 'camila.aluna@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=camilaaluna', 'Aluna com assinatura ativa.', NULL, 7100, 7100, 7100, 360, 8, 12, '2026-03-23', '2026-03-01 09:00:00', '2026-03-23 07:10:00', 1, 'active'),
(1104, 'Daniel Souza', 'Daniel Souza', 'danielaluno', 'daniel.aluno@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=danielaluno', 'Aluno com historico de cursos pagos.', NULL, 6400, 6400, 6400, 330, 7, 10, '2026-03-22', '2026-03-01 09:00:00', '2026-03-22 20:00:00', 1, 'active'),
(1105, 'Elisa Prado', 'Elisa Prado', 'elisaaluna', 'elisa.aluna@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=elisaaluna', 'Aluna com foco em arte e assinatura.', NULL, 5900, 5900, 5900, 300, 7, 9, '2026-03-23', '2026-03-01 09:00:00', '2026-03-23 06:40:00', 1, 'active'),
(1106, 'Fabio Nunes', 'Fabio Nunes', 'fabioaluno', 'fabio.aluno@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=fabioaluno', 'Aluno em transicao para conteudo pago.', NULL, 5200, 5200, 5200, 260, 6, 7, '2026-03-22', '2026-03-01 09:00:00', '2026-03-22 19:10:00', 1, 'active'),
(1107, 'Giovana Reis', 'Giovana Reis', 'giovanaaluna', 'giovana.aluna@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=giovanaaluna', 'Aluna com interesse em Unity e masterclasses.', NULL, 4700, 4700, 4700, 230, 6, 6, '2026-03-22', '2026-03-01 09:00:00', '2026-03-22 17:25:00', 1, 'active'),
(1108, 'Henrique Melo', 'Henrique Melo', 'henriquealuno', 'henrique.aluno@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=henriquealuno', 'Aluno com plano anual ativo.', NULL, 3900, 3900, 3900, 190, 5, 5, '2026-03-22', '2026-03-01 09:00:00', '2026-03-22 12:20:00', 1, 'active'),
(1109, 'Isabela Martins', 'Isabela Martins', 'isabelaaluna', 'isabela.aluna@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=isabelaaluna', 'Aluna com compras avulsas e boa recorrencia.', NULL, 3400, 3400, 3400, 170, 5, 4, '2026-03-21', '2026-03-01 09:00:00', '2026-03-21 21:00:00', 1, 'active'),
(1110, 'Joao Pires', 'Joao Pires', 'joaoaluno', 'joao.aluno@gamedev.demo', '$2y$10$NO8OvO148x.zAdwBPLNZceA36Xnqx8SxG7My4XW3OW2ChQGeAwlHG', 'student', 'https://i.pravatar.cc/300?u=joaoaluno', 'Aluno em cursos gratuitos e trilhas iniciais.', NULL, 2800, 2800, 2800, 150, 4, 3, '2026-03-21', '2026-03-01 09:00:00', '2026-03-21 18:30:00', 1, 'active');

INSERT IGNORE INTO `certificate_templates` (`id`, `name`, `html_template`, `css_styles`, `orientation`, `paper_size`, `is_default`, `is_active`) VALUES
(9501, 'Template Demo Certificado', '<div class=\"certificate\"><h1>Certificado de Conclusao</h1><p>{{student_name}}</p><p>{{course_title}}</p><p>{{completion_date}}</p></div>', '.certificate{font-family:Georgia,serif;text-align:center;padding:48px;border:8px solid #111827}h1{font-size:40px;margin-bottom:24px}p{font-size:18px;margin:10px 0}', 'landscape', 'a4', 1, 1);

INSERT IGNORE INTO `subscription_plans` (
    `id`, `name`, `slug`, `description`, `price_monthly`, `price_annual`, `currency`, `trial_days`,
    `max_courses`, `has_certificates`, `has_downloads`, `has_offline_access`, `has_mentorship`,
    `features`, `sort_order`, `is_popular`, `is_active`
) VALUES
(9601, 'Starter', 'starter', 'Plano de entrada para trilhas livres e cursos curtos.', 19.90, 199.00, 'BRL', 7, 5, 1, 0, 0, 0, '[\"certificados\",\"trilhas-livres\"]', 1, 0, 1),
(9602, 'Pro', 'pro', 'Plano completo para cursos livres e masterclasses.', 39.90, 359.00, 'BRL', 7, NULL, 1, 1, 1, 0, '[\"certificados\",\"downloads\",\"masterclasses\",\"catalogo-ilimitado\"]', 2, 1, 1),
(9603, 'Studio', 'studio', 'Plano anual com foco em estudio, mentoria e biblioteca completa.', 69.90, 599.00, 'BRL', 14, NULL, 1, 1, 1, 1, '[\"catalogo-ilimitado\",\"downloads\",\"mentoria\",\"relatorios\"]', 3, 0, 1);

INSERT IGNORE INTO `courses` (
    `id`, `title`, `slug`, `subtitle`, `description`, `short_description`, `thumbnail`, `image`, `cover_image`,
    `preview_video`, `trailer_url`, `instructor_id`, `category_id`, `level`, `language`, `price`, `original_price`,
    `currency`, `duration_hours`, `xp_reward`, `coin_reward`, `total_lessons`, `total_modules`, `requirements`,
    `what_you_learn`, `target_audience`, `game_engine`, `programming_lang`, `status`, `is_published`, `is_active`,
    `is_featured`, `is_free`, `is_bestseller`, `is_new`, `enrollment_count`, `total_students`, `rating_average`,
    `average_rating`, `rating_count`, `total_reviews`, `completion_rate`, `view_count`, `published_at`
) VALUES
(2001, 'Phaser 3 Essencial', 'phaser-3-essencial', 'Crie um jogo 2D completo no navegador.', '<p>Curso gratuito com foco em setup, cenas, sprites, colisao e publicacao do primeiro jogo em Phaser.</p>', 'Curso gratuito para iniciar no Phaser com projeto pratico.', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 'https://www.youtube.com/watch?v=phaser-demo-trailer', 'https://www.youtube.com/watch?v=phaser-demo-trailer', 1002, 101, 'beginner', 'pt-BR', 0.00, 0.00, 'BRL', 18.0, 480, 60, 4, 2, '[\"Nocoes basicas de logica\",\"PC com navegador moderno\"]', '[\"Criar cenas\",\"Controlar sprites\",\"Publicar no navegador\"]', '[\"Iniciantes em desenvolvimento de jogos\"]', 'phaser', 'JavaScript', 'published', 1, 1, 1, 1, 1, 1, 124, 124, 4.80, 4.80, 42, 42, 71.00, 1820, '2026-01-05 09:00:00'),
(2002, 'Godot 4 para Prototipos', 'godot-4-para-prototipos', 'Valide ideias com velocidade e clareza.', '<p>Curso pago para criar prototipos em Godot 4 usando cenas, nos, sinais e loops de validacao rapidos.</p>', 'Curso pago para montar prototipos jogaveis em Godot 4.', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1200', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1200', 'https://www.youtube.com/watch?v=godot-demo-trailer', 'https://www.youtube.com/watch?v=godot-demo-trailer', 1003, 102, 'beginner', 'pt-BR', 89.90, 109.90, 'BRL', 24.0, 560, 70, 4, 2, '[\"Nocoes de logica\",\"Interesse em prototipacao\"]', '[\"Estruturar cenas\",\"Usar sinais\",\"Validar gameplay\"]', '[\"Desenvolvedores iniciando no Godot\"]', 'godot', 'GDScript', 'published', 1, 1, 1, 0, 0, 1, 82, 82, 4.70, 4.70, 29, 29, 66.00, 1260, '2026-01-07 09:00:00'),
(2003, 'Unity 2D Plataforma Profissional', 'unity-2d-plataforma-profissional', 'Pipeline solido para um platformer comercial.', '<p>Curso pago de longa duracao com arquitetura, fisica 2D, camera, feedback e checkpoint.</p>', 'Curso longo em Unity 2D com foco profissional.', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200', 'https://www.youtube.com/watch?v=unity-demo-trailer', 'https://www.youtube.com/watch?v=unity-demo-trailer', 1004, 103, 'intermediate', 'pt-BR', 149.90, 189.90, 'BRL', 42.0, 760, 90, 4, 2, '[\"Conhecimento basico em C#\",\"Editor Unity instalado\"]', '[\"Criar fase 2D\",\"Usar camera suave\",\"Organizar prefabs\"]', '[\"Alunos que querem um curso longo de Unity\"]', 'unity', 'C#', 'published', 1, 1, 1, 0, 1, 1, 67, 67, 4.90, 4.90, 37, 37, 63.00, 1540, '2026-01-10 09:00:00'),
(2004, 'Pixel Art para Games Indie', 'pixel-art-para-games-indie', 'Direcao visual enxuta para jogos autorais.', '<p>Curso gratuito com foco em paleta, silhueta, tilesets, animacao basica e consistencia visual.</p>', 'Curso gratuito de pixel art aplicada a jogos.', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200', 'https://www.youtube.com/watch?v=pixel-demo-trailer', 'https://www.youtube.com/watch?v=pixel-demo-trailer', 1005, 104, 'beginner', 'pt-BR', 0.00, 0.00, 'BRL', 16.0, 430, 55, 4, 2, '[\"Editor de imagem simples\",\"Vontade de estudar fundamentos visuais\"]', '[\"Criar tilesets\",\"Animar sprites\",\"Definir paletas\"]', '[\"Artistas iniciantes para jogos indie\"]', 'none', 'Pixel Art', 'published', 1, 1, 1, 1, 0, 1, 94, 94, 4.85, 4.85, 31, 31, 74.00, 1415, '2026-01-12 09:00:00'),
(2005, 'Game Design na Pratica', 'game-design-na-pratica', 'Sistemas, economia e progressao em producao.', '<p>Curso pago de longa duracao para desenhar core loop, recompensa, economia e curva de dificuldade.</p>', 'Curso longo para estruturar sistemas de game design.', 'https://images.unsplash.com/photo-1542751110-97427bbecf20?w=800', 'https://images.unsplash.com/photo-1542751110-97427bbecf20?w=1200', 'https://images.unsplash.com/photo-1542751110-97427bbecf20?w=1200', 'https://www.youtube.com/watch?v=design-demo-trailer', 'https://www.youtube.com/watch?v=design-demo-trailer', 1006, 105, 'intermediate', 'pt-BR', 129.90, 159.90, 'BRL', 48.0, 720, 85, 4, 2, '[\"Interesse em sistemas e economia\",\"Disposicao para documentar\"]', '[\"Escrever GDD\",\"Balancear recompensa\",\"Criar progressao\"]', '[\"Designers e lideres de produto para jogos\"]', 'none', 'Design', 'published', 1, 1, 0, 0, 1, 1, 58, 58, 4.78, 4.78, 24, 24, 61.00, 1120, '2026-01-14 09:00:00'),
(2101, 'Masterclass Monetizacao Indie', 'masterclass-monetizacao-indie', 'Oferta, MRR e retencao para jogos independentes.', '<p>Masterclass paga com foco em monetizacao, precificacao e estrategia de receita.</p>', 'Masterclass paga sobre monetizacao e receita recorrente.', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=800', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=1200', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=1200', 'https://www.youtube.com/watch?v=monetizacao-masterclass', 'https://www.youtube.com/watch?v=monetizacao-masterclass', 1006, 106, 'intermediate', 'pt-BR', 79.90, 99.90, 'BRL', 6.0, 220, 25, 4, 2, '[\"Projeto ou ideia de jogo em andamento\"]', '[\"Definir oferta\",\"Estimar MRR\",\"Planejar retencao\"]', '[\"Studios indie e produtores\"]', 'none', 'Business', 'published', 1, 1, 1, 0, 0, 1, 39, 39, 4.92, 4.92, 16, 16, 69.00, 860, '2026-02-01 09:00:00'),
(2102, 'Masterclass Direcao de Arte 2D', 'masterclass-direcao-de-arte-2d', 'Moodboard, coerencia visual e identidade.', '<p>Masterclass paga para montar uma direcao de arte clara e aplicavel a um projeto real.</p>', 'Masterclass paga sobre direcao de arte 2D.', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=800', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200', 'https://www.youtube.com/watch?v=arte-masterclass', 'https://www.youtube.com/watch?v=arte-masterclass', 1005, 107, 'intermediate', 'pt-BR', 69.90, 89.90, 'BRL', 8.0, 240, 30, 4, 2, '[\"Interesse em arte 2D\",\"Referencias visuais basicas\"]', '[\"Construir moodboards\",\"Definir paleta\",\"Guiar producao visual\"]', '[\"Artistas e equipes de arte\"]', 'none', 'Art', 'published', 1, 1, 0, 0, 0, 1, 34, 34, 4.88, 4.88, 15, 15, 68.00, 740, '2026-02-03 09:00:00'),
(2103, 'Masterclass IA para NPCs', 'masterclass-ia-para-npcs', 'FSM, sensores e reacao para personagens.', '<p>Masterclass gratuita para estruturar comportamento de NPCs com estados e transicoes.</p>', 'Masterclass gratuita sobre IA para NPCs.', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=1200', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=1200', 'https://www.youtube.com/watch?v=ia-masterclass', 'https://www.youtube.com/watch?v=ia-masterclass', 1004, 108, 'intermediate', 'pt-BR', 0.00, 0.00, 'BRL', 5.0, 210, 24, 4, 2, '[\"Nocoes de programacao\"]', '[\"Criar FSM\",\"Usar sensores\",\"Definir reacoes\"]', '[\"Programadores e designers tecnicos\"]', 'unity', 'C#', 'published', 1, 1, 1, 1, 0, 1, 51, 51, 4.74, 4.74, 18, 18, 73.00, 920, '2026-02-05 09:00:00'),
(2104, 'Masterclass Prototipacao Rapida', 'masterclass-prototipacao-rapida', 'Valide a mecanica certa antes de escalar.', '<p>Masterclass paga para cortar escopo, testar cedo e iterar com prototipos enxutos.</p>', 'Masterclass paga sobre validacao rapida de mecanicas.', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200', 'https://www.youtube.com/watch?v=proto-masterclass', 'https://www.youtube.com/watch?v=proto-masterclass', 1003, 109, 'beginner', 'pt-BR', 59.90, 79.90, 'BRL', 7.0, 210, 24, 4, 2, '[\"Projeto em ideacao ou pre-producao\"]', '[\"Definir hipoteses\",\"Testar mecanicas\",\"Descartar excesso\"]', '[\"Equipes pequenas e criadores solo\"]', 'godot', 'GDScript', 'published', 1, 1, 0, 0, 0, 1, 43, 43, 4.79, 4.79, 17, 17, 67.00, 780, '2026-02-07 09:00:00'),
(2105, 'Masterclass Publicacao na Steam', 'masterclass-publicacao-na-steam', 'Checklist, wishlist e conversao para lancar melhor.', '<p>Masterclass paga sobre assets de loja, capsule art, pagina da Steam e plano de lancamento.</p>', 'Masterclass paga sobre publicacao na Steam.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800', 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200', 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200', 'https://www.youtube.com/watch?v=steam-masterclass', 'https://www.youtube.com/watch?v=steam-masterclass', 1002, 110, 'intermediate', 'pt-BR', 99.90, 129.90, 'BRL', 9.0, 260, 32, 4, 2, '[\"Jogo em fase de polimento ou marketing\"]', '[\"Montar checklist\",\"Melhorar wishlist\",\"Estruturar lancamento\"]', '[\"Studios e devs proximos do lancamento\"]', 'none', 'Marketing', 'published', 1, 1, 1, 0, 0, 1, 37, 37, 4.95, 4.95, 19, 19, 72.00, 990, '2026-02-09 09:00:00');

INSERT IGNORE INTO `blog_posts` (
    `id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `image`, `cover_image`,
    `author_id`, `category_id`, `status`, `is_featured`, `allow_comments`, `view_count`,
    `reading_time`, `published_at`
) VALUES
(3001, 'Bem-vindo a GameDev Academy', 'bem-vindo-a-gamedev-academy', 'Conheca a plataforma, seus formatos de curso e como comecar.', '<p>A GameDev Academy nasce para reunir cursos gratuitos, cursos pagos, masterclasses e trilhas de longa duracao em um unico ecossistema.</p><p>O objetivo desta plataforma demo e mostrar a jornada completa do aluno e do instrutor.</p>', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 1001, 112, 'published', 1, 1, 420, 4, '2026-03-01 10:00:00'),
(3002, 'Novidades da plataforma demo', 'novidades-da-plataforma-demo', 'Veja o que esta habilitado no ambiente demonstrativo de instalacao.', '<p>O pacote demo inclui cursos, masterclasses, alunos com ranking, financeiro basico, despesas e acessos prontos para teste.</p>', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200', 1001, 112, 'published', 1, 1, 265, 3, '2026-03-05 12:00:00'),
(3003, 'O que e a GameDev Academy', 'o-que-e-a-gamedev-academy', 'Entenda o posicionamento da plataforma e seus modelos de produto.', '<p>A plataforma foi desenhada para operar com cursos livres, cursos longos, masterclasses, assinaturas e repasses para instrutores.</p>', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200', 1001, 112, 'published', 0, 1, 198, 4, '2026-03-08 09:30:00'),
(3004, 'Certificados e conclusao de cursos', 'certificados-e-conclusao-de-cursos', 'Como funcionam os certificados em cursos gratuitos, pagos e assinaturas.', '<p>Cursos gratuitos liberam certificado ao concluir. Cursos pagos exigem conclusao e pagamento confirmado. Em acessos por assinatura, o plano precisa estar ativo.</p>', 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200', 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200', 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200', 1001, 112, 'published', 0, 1, 153, 3, '2026-03-12 11:15:00'),
(3005, 'Masterclasses em destaque', 'masterclasses-em-destaque', 'Conheca as masterclasses criadas para a base demonstrativa.', '<p>As masterclasses demo cobrem monetizacao, direcao de arte, IA para NPCs, prototipacao rapida e publicacao na Steam.</p>', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=1200', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=1200', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=1200', 1002, 112, 'published', 0, 1, 129, 2, '2026-03-15 15:20:00');

INSERT IGNORE INTO `course_modules` (`id`, `course_id`, `title`, `description`, `sort_order`, `is_free_preview`, `is_published`, `duration_minutes`, `xp_reward`, `lesson_count`) VALUES
(5001, 2001, 'Fundamentos do Phaser 3', 'Primeira parte do programa oficial de Phaser 3 Essencial.', 1, 1, 1, 180, 80, 2),
(5002, 2001, 'Projeto Final em Phaser 3', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5003, 2002, 'Base do Godot 4', 'Primeira parte do programa oficial de Godot 4 para Prototipos.', 1, 1, 1, 180, 80, 2),
(5004, 2002, 'Prototipo Jogavel', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5005, 2003, 'Arquitetura do Projeto 2D', 'Primeira parte do programa oficial de Unity 2D Plataforma Profissional.', 1, 1, 1, 180, 80, 2),
(5006, 2003, 'Loop de Gameplay', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5007, 2004, 'Fundamentos Visuais', 'Primeira parte do programa oficial de Pixel Art para Games Indie.', 1, 1, 1, 180, 80, 2),
(5008, 2004, 'Pipeline de Assets', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5009, 2005, 'Core Loop e Metas', 'Primeira parte do programa oficial de Game Design na Pratica.', 1, 1, 1, 180, 80, 2),
(5010, 2005, 'Balanceamento e Testes', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5011, 2101, 'Oferta e Posicionamento', 'Primeira parte do programa oficial de Masterclass Monetizacao Indie.', 1, 1, 1, 180, 80, 2),
(5012, 2101, 'Receita Recorrente', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5013, 2102, 'Identidade Visual', 'Primeira parte do programa oficial de Masterclass Direcao de Arte 2D.', 1, 1, 1, 180, 80, 2),
(5014, 2102, 'Aplicacao em Cena', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5015, 2103, 'Estados e Regras', 'Primeira parte do programa oficial de Masterclass IA para NPCs.', 1, 1, 1, 180, 80, 2),
(5016, 2103, 'Comportamentos Emergentes', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5017, 2104, 'Hipoteses de Gameplay', 'Primeira parte do programa oficial de Masterclass Prototipacao Rapida.', 1, 1, 1, 180, 80, 2),
(5018, 2104, 'Validacao Rapida', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2),
(5019, 2105, 'Preparacao Comercial', 'Primeira parte do programa oficial de Masterclass Publicacao na Steam.', 1, 1, 1, 180, 80, 2),
(5020, 2105, 'Lancamento e Pos-lancamento', 'Segunda parte com projeto aplicado e consolidacao pratica.', 2, 0, 1, 240, 90, 2);

INSERT IGNORE INTO `course_lessons` (
    `id`, `module_id`, `course_id`, `title`, `summary`, `slug`, `content_type`, `content`,
    `video_url`, `video_provider`, `video_duration`, `xp_reward`, `coin_reward`, `sort_order`,
    `is_free_preview`, `is_published`, `is_mandatory`, `completion_rule`
) VALUES
(6001, 5001, 2001, 'Boas-vindas e Setup do Curso', 'Configure o ambiente e entenda o fluxo de estudo do curso.', 'phaser-3-essencial-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=phaser-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6002, 5001, 2001, 'Quiz de Fundamentos do Phaser', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'phaser-3-essencial-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6003, 5002, 2001, 'Construindo o Jogo Base', 'Monte a estrutura jogavel do projeto principal.', 'phaser-3-essencial-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=phaser-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6004, 5002, 2001, 'Atividade Pratica do Curso', 'Entrega pratica para validacao do aprendizado do curso.', 'phaser-3-essencial-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6005, 5003, 2002, 'Instalacao e Primeiros Nos', 'Conheca a interface, cenas e nos do Godot 4.', 'godot-4-para-prototipos-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=godot-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6006, 5003, 2002, 'Quiz de Estrutura no Godot', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'godot-4-para-prototipos-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6007, 5004, 2002, 'Montando um Prototipo Rapido', 'Transforme um conceito em um prototipo com gameplay valida.', 'godot-4-para-prototipos-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=godot-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6008, 5004, 2002, 'Atividade Pratica do Curso', 'Entrega pratica para validacao do aprendizado do curso.', 'godot-4-para-prototipos-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6009, 5005, 2003, 'Planejamento da Cena Principal', 'Estruture o projeto 2D com prefabs e pastas consistentes.', 'unity-2d-plataforma-profissional-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=unity-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6010, 5005, 2003, 'Quiz de Componentes 2D', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'unity-2d-plataforma-profissional-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6011, 5006, 2003, 'Fisica, Camera e Feedback', 'Implemente movimento, camera e sensacao de impacto.', 'unity-2d-plataforma-profissional-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=unity-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6012, 5006, 2003, 'Atividade Pratica do Curso', 'Entrega pratica para validacao do aprendizado do curso.', 'unity-2d-plataforma-profissional-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6013, 5007, 2004, 'Paleta, Silhueta e Leitura', 'Defina formas, contraste e consistencia visual para o projeto.', 'pixel-art-para-games-indie-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=pixel-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6014, 5007, 2004, 'Quiz de Direcao de Arte', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'pixel-art-para-games-indie-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6015, 5008, 2004, 'Animando Personagens e Tiles', 'Produza assets reutilizaveis para cenarios e personagens.', 'pixel-art-para-games-indie-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=pixel-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6016, 5008, 2004, 'Atividade Pratica do Curso', 'Entrega pratica para validacao do aprendizado do curso.', 'pixel-art-para-games-indie-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6017, 5009, 2005, 'Definindo a Experiencia do Jogador', 'Mapeie objetivo, desafio e recompensa de um jogo escalavel.', 'game-design-na-pratica-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=gamedesign-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6018, 5009, 2005, 'Quiz de Design de Sistemas', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'game-design-na-pratica-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6019, 5010, 2005, 'Balanceando Progressao e Recompensa', 'Ajuste progressao, economia e curva de dificuldade.', 'game-design-na-pratica-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=gamedesign-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6020, 5010, 2005, 'Atividade Pratica do Curso', 'Entrega pratica para validacao do aprendizado do curso.', 'game-design-na-pratica-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6021, 5011, 2101, 'Modelos de Receita para Indies', 'Compare premium, DLC, assinaturas e monetizacao hibrida.', 'masterclass-monetizacao-indie-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=monetizacao-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6022, 5011, 2101, 'Quiz de Monetizacao', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'masterclass-monetizacao-indie-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6023, 5012, 2101, 'Precificacao e Retencao', 'Desenhe um plano de receita alinhado ao publico do jogo.', 'masterclass-monetizacao-indie-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=monetizacao-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6024, 5012, 2101, 'Atividade Pratica da Masterclass', 'Entrega pratica para validacao do aprendizado do curso.', 'masterclass-monetizacao-indie-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6025, 5013, 2102, 'Moodboard e Referencias', 'Construa a linguagem visual de um projeto 2D memoravel.', 'masterclass-direcao-de-arte-2d-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=arte-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6026, 5013, 2102, 'Quiz de Direcao de Arte', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'masterclass-direcao-de-arte-2d-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6027, 5014, 2102, 'Coerencia Visual em Assets', 'Aplique cor, forma e ritmo visual em cenarios e personagens.', 'masterclass-direcao-de-arte-2d-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=arte-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6028, 5014, 2102, 'Atividade Pratica da Masterclass', 'Entrega pratica para validacao do aprendizado do curso.', 'masterclass-direcao-de-arte-2d-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6029, 5015, 2103, 'FSM e Tomada de Decisao', 'Defina estados, sensores e gatilhos para agentes jogaveis.', 'masterclass-ia-para-npcs-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=ia-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6030, 5015, 2103, 'Quiz de IA para NPCs', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'masterclass-ia-para-npcs-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6031, 5016, 2103, 'Percepcao, Patrulha e Reacao', 'Implemente reacoes criveis com transicoes claras entre estados.', 'masterclass-ia-para-npcs-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=ia-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6032, 5016, 2103, 'Atividade Pratica da Masterclass', 'Entrega pratica para validacao do aprendizado do curso.', 'masterclass-ia-para-npcs-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6033, 5017, 2104, 'Escopo de Prototipos Enxutos', 'Crie prototipos focados na pergunta certa de design.', 'masterclass-prototipacao-rapida-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=proto-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6034, 5017, 2104, 'Quiz de Prototipacao', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'masterclass-prototipacao-rapida-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6035, 5018, 2104, 'Teste, Iteracao e Corte', 'Use testes curtos para validar mecanicas e descartar excesso.', 'masterclass-prototipacao-rapida-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=proto-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6036, 5018, 2104, 'Atividade Pratica da Masterclass', 'Entrega pratica para validacao do aprendizado do curso.', 'masterclass-prototipacao-rapida-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual'),
(6037, 5019, 2105, 'Checklist de Publicacao', 'Organize entregaveis de loja, marketing e operacao do lancamento.', 'masterclass-publicacao-na-steam-inicio', 'video', '<p>Aula inicial com contexto, setup e objetivos do curso.</p>', 'https://www.youtube.com/watch?v=steam-demo-01', 'youtube', 18, 20, 2, 1, 1, 1, 1, 'video_watched'),
(6038, 5019, 2105, 'Quiz de Go To Market', 'Atividade avaliativa para consolidar os fundamentos vistos no modulo.', 'masterclass-publicacao-na-steam-quiz', 'quiz', '<p>Quiz avaliativo do modulo 1.</p>', NULL, NULL, 0, 25, 3, 2, 0, 1, 1, 'quiz_passed'),
(6039, 5020, 2105, 'Pagina da Steam e Conversao', 'Estruture a pagina da Steam para elevar wishlist e conversao.', 'masterclass-publicacao-na-steam-projeto', 'video', '<p>Aula pratica com demonstracao e aplicacao no projeto.</p>', 'https://www.youtube.com/watch?v=steam-demo-02', 'youtube', 24, 25, 3, 1, 0, 1, 1, 'video_watched'),
(6040, 5020, 2105, 'Atividade Pratica da Masterclass', 'Entrega pratica para validacao do aprendizado do curso.', 'masterclass-publicacao-na-steam-atividade', 'assignment', '<p>Entrega obrigatoria para consolidacao do curso.</p>', NULL, NULL, 0, 30, 5, 2, 0, 1, 1, 'manual');

INSERT IGNORE INTO `quizzes` (
    `id`, `lesson_id`, `title`, `description`, `time_limit`, `pass_percentage`,
    `max_attempts`, `shuffle_questions`, `shuffle_options`, `show_correct_answers`,
    `show_explanation`, `question_count`, `is_active`
) VALUES
(7001, 6002, 'Quiz de Fundamentos do Phaser', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7002, 6006, 'Quiz de Estrutura no Godot', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7003, 6010, 'Quiz de Componentes 2D', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7004, 6014, 'Quiz de Direcao de Arte', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7005, 6018, 'Quiz de Design de Sistemas', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7006, 6022, 'Quiz de Monetizacao', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7007, 6026, 'Quiz de Direcao de Arte', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7008, 6030, 'Quiz de IA para NPCs', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7009, 6034, 'Quiz de Prototipacao', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1),
(7010, 6038, 'Quiz de Go To Market', 'Questionario avaliativo do modulo inicial.', 20, 70.00, 3, 1, 1, 1, 1, 1, 1);

INSERT IGNORE INTO `quiz_questions` (`id`, `quiz_id`, `question_type`, `question_text`, `explanation`, `points`, `sort_order`) VALUES
(7101, 7001, 'multiple_choice', 'Qual recurso do Phaser ajuda a organizar cenas e estados do jogo?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7102, 7002, 'multiple_choice', 'No Godot, qual unidade organiza objetos na arvore da cena?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7103, 7003, 'multiple_choice', 'Qual componente da Unity 2D controla interacoes fisicas em personagens?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7104, 7004, 'multiple_choice', 'Qual principio melhora a leitura de um personagem em pixel art?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7105, 7005, 'multiple_choice', 'Qual documento resume objetivos, sistemas e escopo de um jogo?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7106, 7006, 'multiple_choice', 'Qual indicador mede receita recorrente mensal de assinaturas?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7107, 7007, 'multiple_choice', 'Qual ferramenta ajuda a alinhar referencias e tom visual de um projeto?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7108, 7008, 'multiple_choice', 'Qual modelo organiza comportamentos por estados e transicoes?', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7109, 7009, 'multiple_choice', 'O objetivo principal de um prototipo rapido e:', 'Questao usada para validar entendimento central do modulo.', 10, 1),
(7110, 7010, 'multiple_choice', 'Qual ativo influencia diretamente a conversao da pagina da Steam?', 'Questao usada para validar entendimento central do modulo.', 10, 1);

INSERT IGNORE INTO `quiz_options` (`id`, `question_id`, `option_text`, `is_correct`, `sort_order`) VALUES
(7201, 7101, 'Scene Manager', 1, 1),
(7202, 7101, 'Sprite Sheet', 0, 2),
(7203, 7101, 'Tile Palette', 0, 3),
(7204, 7101, 'Audio Tag', 0, 4),
(7205, 7102, 'Node', 1, 1),
(7206, 7102, 'Signal Bus', 0, 2),
(7207, 7102, 'Package', 0, 3),
(7208, 7102, 'Prefab', 0, 4),
(7209, 7103, 'Rigidbody2D', 1, 1),
(7210, 7103, 'Animator State', 0, 2),
(7211, 7103, 'Mesh Filter', 0, 3),
(7212, 7103, 'Audio Mixer', 0, 4),
(7213, 7104, 'Silhueta clara', 1, 1),
(7214, 7104, 'Mais detalhes pequenos', 0, 2),
(7215, 7104, 'Sombras aleatorias', 0, 3),
(7216, 7104, 'Paleta sem contraste', 0, 4),
(7217, 7105, 'Game Design Document', 1, 1),
(7218, 7105, 'Patch Notes', 0, 2),
(7219, 7105, 'Render Queue', 0, 3),
(7220, 7105, 'Asset Bundle', 0, 4),
(7221, 7106, 'MRR', 1, 1),
(7222, 7106, 'CTR', 0, 2),
(7223, 7106, 'DAU', 0, 3),
(7224, 7106, 'CPA', 0, 4),
(7225, 7107, 'Moodboard', 1, 1),
(7226, 7107, 'Benchmark de FPS', 0, 2),
(7227, 7107, 'Tabela fiscal', 0, 3),
(7228, 7107, 'Mapa de colisao', 0, 4),
(7229, 7108, 'Finite State Machine', 1, 1),
(7230, 7108, 'Color Palette', 0, 2),
(7231, 7108, 'Sprite Atlas', 0, 3),
(7232, 7108, 'Audio Bus', 0, 4),
(7233, 7109, 'Validar uma hipotese', 1, 1),
(7234, 7109, 'Fechar a versao final', 0, 2),
(7235, 7109, 'Publicar o jogo', 0, 3),
(7236, 7109, 'Definir campanhas de ads', 0, 4),
(7237, 7110, 'Capsule art', 1, 1),
(7238, 7110, 'Arquivo PSD bruto', 0, 2),
(7239, 7110, 'Planilha fiscal', 0, 3),
(7240, 7110, 'Sprite de debug', 0, 4);

INSERT IGNORE INTO `assignments` (
    `id`, `lesson_id`, `title`, `description`, `instructions`, `max_score`,
    `due_days`, `allow_late`, `submission_type`, `allowed_extensions`, `is_active`
) VALUES
(7301, 6004, 'Atividade Pratica do Curso', 'Publique um prototipo funcional com menu inicial, fase jogavel e tela de game over.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'github', NULL, 1),
(7302, 6008, 'Atividade Pratica do Curso', 'Entregue um prototipo em Godot com menu, fase curta e checkpoint.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'github', NULL, 1),
(7303, 6012, 'Atividade Pratica do Curso', 'Crie uma fase 2D jogavel com checkpoints, camera suave e coleta de itens.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'github', NULL, 1),
(7304, 6016, 'Atividade Pratica do Curso', 'Envie um kit visual com personagem, tileset e animacao curta em pixel art.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'file', '[\"zip\",\"png\"]', 1),
(7305, 6020, 'Atividade Pratica do Curso', 'Apresente um GDD enxuto com core loop, monetizacao e mapa de progressao.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'url', NULL, 1),
(7306, 6024, 'Atividade Pratica da Masterclass', 'Escreva um plano de monetizacao com oferta, ticket medio e metas de retencao.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'text', NULL, 1),
(7307, 6028, 'Atividade Pratica da Masterclass', 'Monte uma biblia visual curta com referencias, paleta e mockup de cena.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'file', '[\"zip\",\"png\",\"pdf\"]', 1),
(7308, 6032, 'Atividade Pratica da Masterclass', 'Implemente um NPC com patrulha, alerta e perseguicao documentados.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'github', NULL, 1),
(7309, 6036, 'Atividade Pratica da Masterclass', 'Suba um prototipo enxuto com uma mecanica principal validada em teste curto.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'github', NULL, 1),
(7310, 6040, 'Atividade Pratica da Masterclass', 'Descreva o plano de lancamento com wishlist target, assets e cronograma.', 'Organize a entrega, documente as decisoes e envie evidencias do resultado final.', 100, 14, 1, 'text', NULL, 1);

INSERT IGNORE INTO `subscriptions` (
    `id`, `user_id`, `plan_id`, `status`, `billing_cycle`, `amount`, `currency`,
    `gateway`, `gateway_subscription_id`, `trial_ends_at`, `current_period_start`,
    `current_period_end`, `started_at`
) VALUES
(9701, 1103, 9602, 'active', 'monthly', 39.90, 'BRL', 'demo_gateway', 'sub_demo_1103', NULL, '2026-03-04 00:00:00', '2026-04-04 00:00:00', '2026-03-04 00:00:00'),
(9702, 1105, 9602, 'active', 'monthly', 39.90, 'BRL', 'demo_gateway', 'sub_demo_1105', NULL, '2026-03-06 00:00:00', '2026-04-06 00:00:00', '2026-03-06 00:00:00'),
(9703, 1108, 9603, 'active', 'annual', 599.00, 'BRL', 'demo_gateway', 'sub_demo_1108', NULL, '2026-03-10 00:00:00', '2027-03-10 00:00:00', '2026-03-10 00:00:00'),
(9704, 1110, 9601, 'trialing', 'monthly', 19.90, 'BRL', 'demo_gateway', 'sub_demo_1110', '2026-03-29 00:00:00', '2026-03-22 00:00:00', '2026-04-22 00:00:00', '2026-03-22 00:00:00');

INSERT IGNORE INTO `payments` (
    `id`, `user_id`, `course_id`, `amount`, `original_amount`, `discount_amount`, `currency`,
    `payment_method`, `payment_gateway`, `gateway_transaction_id`, `status`, `invoice_number`,
    `paid_at`, `created_at`
) VALUES
(9001, 1101, 2003, 149.90, 149.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9001', 'completed', 'INV-9001', '2026-01-10 10:00:00', '2026-01-10 10:00:00'),
(9002, 1101, 2105, 99.90, 99.90, 0.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9002', 'completed', 'INV-9002', '2026-02-02 09:05:00', '2026-02-02 09:05:00'),
(9003, 1102, 2002, 89.90, 89.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9003', 'completed', 'INV-9003', '2026-01-07 09:10:00', '2026-01-07 09:10:00'),
(9004, 1102, 2005, 129.90, 129.90, 0.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9004', 'completed', 'INV-9004', '2026-01-20 10:10:00', '2026-01-20 10:10:00'),
(9005, 1103, 2104, 59.90, 59.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9005', 'completed', 'INV-9005', '2026-02-01 12:10:00', '2026-02-01 12:10:00'),
(9006, 1104, 2002, 89.90, 89.90, 10.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9006', 'completed', 'INV-9006', '2026-01-14 09:10:00', '2026-01-14 09:10:00'),
(9007, 1104, 2101, 79.90, 79.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9007', 'completed', 'INV-9007', '2026-02-05 08:35:00', '2026-02-05 08:35:00'),
(9008, 1106, 2005, 129.90, 129.90, 10.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9008', 'completed', 'INV-9008', '2026-02-01 10:35:00', '2026-02-01 10:35:00'),
(9009, 1107, 2003, 149.90, 149.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9009', 'completed', 'INV-9009', '2026-01-09 08:10:00', '2026-01-09 08:10:00'),
(9010, 1107, 2102, 69.90, 69.90, 0.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9010', 'completed', 'INV-9010', '2026-02-09 11:10:00', '2026-02-09 11:10:00'),
(9011, 1108, 2104, 59.90, 59.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9011', 'completed', 'INV-9011', '2026-02-07 12:10:00', '2026-02-07 12:10:00'),
(9012, 1109, 2002, 89.90, 89.90, 0.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9012', 'completed', 'INV-9012', '2026-01-25 11:10:00', '2026-01-25 11:10:00'),
(9013, 1101, 2101, 79.90, 79.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9013', 'completed', 'INV-9013', '2026-03-01 10:05:00', '2026-03-01 10:05:00'),
(9014, 1102, 2003, 149.90, 149.90, 20.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9014', 'completed', 'INV-9014', '2026-03-02 10:05:00', '2026-03-02 10:05:00'),
(9015, 1104, 2005, 129.90, 129.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9015', 'completed', 'INV-9015', '2026-03-03 09:05:00', '2026-03-03 09:05:00'),
(9016, 1106, 2002, 89.90, 89.90, 0.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9016', 'completed', 'INV-9016', '2026-03-08 10:05:00', '2026-03-08 10:05:00'),
(9017, 1107, 2104, 59.90, 59.90, 0.00, 'BRL', 'pix', 'demo_gateway', 'pay_demo_9017', 'completed', 'INV-9017', '2026-03-09 12:05:00', '2026-03-09 12:05:00'),
(9018, 1109, 2105, 99.90, 99.90, 0.00, 'BRL', 'credit_card', 'demo_gateway', 'pay_demo_9018', 'completed', 'INV-9018', '2026-03-11 08:05:00', '2026-03-11 08:05:00');

INSERT IGNORE INTO `enrollments` (
    `id`, `user_id`, `course_id`, `status`, `progress_percent`, `lessons_completed`,
    `last_lesson_id`, `last_accessed_at`, `enrolled_at`, `completed_at`, `expires_at`,
    `payment_id`, `certificate_issued`, `source`
) VALUES
(8001, 1101, 2001, 'completed', 100.00, 4, 6004, '2026-01-28 16:00:00', '2026-01-08 10:00:00', '2026-01-28 16:00:00', NULL, NULL, 1, 'direct'),
(8002, 1101, 2003, 'completed', 100.00, 4, 6012, '2026-02-20 19:00:00', '2026-01-10 10:00:00', '2026-02-20 19:00:00', NULL, 9001, 1, 'direct'),
(8003, 1101, 2105, 'completed', 100.00, 4, 6040, '2026-02-26 20:00:00', '2026-02-02 09:00:00', '2026-02-26 20:00:00', NULL, 9002, 1, 'direct'),
(8004, 1102, 2002, 'completed', 100.00, 4, 6008, '2026-01-24 17:00:00', '2026-01-07 09:00:00', '2026-01-24 17:00:00', NULL, 9003, 1, 'direct'),
(8005, 1102, 2005, 'completed', 100.00, 4, 6020, '2026-03-01 21:00:00', '2026-01-20 10:00:00', '2026-03-01 21:00:00', NULL, 9004, 1, 'direct'),
(8006, 1103, 2001, 'completed', 100.00, 4, 6004, '2026-01-18 14:00:00', '2026-01-05 10:00:00', '2026-01-18 14:00:00', NULL, NULL, 0, 'direct'),
(8007, 1103, 2004, 'completed', 100.00, 4, 6016, '2026-02-02 16:00:00', '2026-01-15 11:00:00', '2026-02-02 16:00:00', NULL, NULL, 0, 'direct'),
(8008, 1103, 2104, 'completed', 100.00, 4, 6036, '2026-02-12 18:00:00', '2026-02-01 12:00:00', '2026-02-12 18:00:00', NULL, 9005, 1, 'direct'),
(8009, 1104, 2002, 'completed', 100.00, 4, 6008, '2026-02-01 17:00:00', '2026-01-14 09:00:00', '2026-02-01 17:00:00', NULL, 9006, 1, 'direct'),
(8010, 1104, 2101, 'completed', 100.00, 4, 6024, '2026-02-18 20:30:00', '2026-02-05 08:30:00', '2026-02-18 20:30:00', NULL, 9007, 1, 'direct'),
(8011, 1105, 2004, 'completed', 100.00, 4, 6016, '2026-02-08 17:00:00', '2026-01-18 14:00:00', '2026-02-08 17:00:00', NULL, NULL, 0, 'direct'),
(8012, 1105, 2103, 'completed', 100.00, 4, 6032, '2026-02-15 15:00:00', '2026-02-03 13:00:00', '2026-02-15 15:00:00', NULL, NULL, 1, 'direct'),
(8013, 1106, 2001, 'completed', 100.00, 4, 6004, '2026-01-25 18:00:00', '2026-01-11 10:00:00', '2026-01-25 18:00:00', NULL, NULL, 1, 'direct'),
(8014, 1106, 2005, 'completed', 100.00, 4, 6020, '2026-03-04 20:00:00', '2026-02-01 10:30:00', '2026-03-04 20:00:00', NULL, 9008, 1, 'direct'),
(8015, 1107, 2003, 'completed', 100.00, 4, 6012, '2026-02-19 19:00:00', '2026-01-09 08:00:00', '2026-02-19 19:00:00', NULL, 9009, 1, 'direct'),
(8016, 1107, 2102, 'completed', 100.00, 4, 6028, '2026-02-27 18:30:00', '2026-02-09 11:00:00', '2026-02-27 18:30:00', NULL, 9010, 1, 'direct'),
(8017, 1108, 2004, 'completed', 100.00, 4, 6016, '2026-01-30 16:30:00', '2026-01-12 09:30:00', '2026-01-30 16:30:00', NULL, NULL, 0, 'direct'),
(8018, 1108, 2104, 'completed', 100.00, 4, 6036, '2026-02-21 17:00:00', '2026-02-07 12:00:00', '2026-02-21 17:00:00', NULL, 9011, 1, 'direct'),
(8019, 1109, 2001, 'completed', 100.00, 4, 6004, '2026-01-29 18:00:00', '2026-01-16 08:00:00', '2026-01-29 18:00:00', NULL, NULL, 0, 'direct'),
(8020, 1109, 2002, 'completed', 100.00, 4, 6008, '2026-02-14 19:00:00', '2026-01-25 11:00:00', '2026-02-14 19:00:00', NULL, 9012, 1, 'direct'),
(8021, 1110, 2103, 'completed', 100.00, 4, 6032, '2026-02-22 14:00:00', '2026-02-10 10:00:00', '2026-02-22 14:00:00', NULL, NULL, 1, 'direct'),
(8022, 1101, 2101, 'active', 52.50, 2, 6022, '2026-03-22 21:00:00', '2026-03-01 10:00:00', NULL, NULL, 9013, 0, 'direct'),
(8023, 1102, 2003, 'active', 25.00, 1, 6009, '2026-03-22 20:00:00', '2026-03-02 10:00:00', NULL, NULL, 9014, 0, 'direct'),
(8024, 1103, 2105, 'active', 50.00, 2, 6038, '2026-03-23 07:00:00', '2026-03-04 11:00:00', NULL, '2026-04-04 11:00:00', NULL, 0, 'subscription'),
(8025, 1104, 2005, 'active', 50.00, 2, 6018, '2026-03-22 18:30:00', '2026-03-03 09:00:00', NULL, NULL, 9015, 0, 'direct'),
(8026, 1105, 2102, 'active', 25.00, 1, 6025, '2026-03-23 06:30:00', '2026-03-06 10:00:00', NULL, '2026-04-06 10:00:00', NULL, 0, 'subscription'),
(8027, 1106, 2002, 'active', 25.00, 1, 6005, '2026-03-22 18:00:00', '2026-03-08 10:00:00', NULL, NULL, 9016, 0, 'direct'),
(8028, 1107, 2104, 'active', 50.00, 2, 6034, '2026-03-22 16:45:00', '2026-03-09 12:00:00', NULL, NULL, 9017, 0, 'direct'),
(8029, 1108, 2003, 'active', 50.00, 2, 6010, '2026-03-22 12:15:00', '2026-03-10 08:00:00', NULL, '2027-03-10 08:00:00', NULL, 0, 'subscription'),
(8030, 1109, 2105, 'active', 25.00, 1, 6037, '2026-03-21 21:00:00', '2026-03-11 08:00:00', NULL, NULL, 9018, 0, 'direct'),
(8031, 1110, 2004, 'active', 50.00, 2, 6014, '2026-03-21 18:20:00', '2026-03-12 09:00:00', NULL, NULL, NULL, 0, 'direct');

INSERT IGNORE INTO `lesson_progress` (
    `id`, `user_id`, `lesson_id`, `course_id`, `status`, `is_completed`, `watch_time`,
    `last_position`, `completed_at`, `created_at`, `updated_at`
) VALUES
(8501, 1101, 6021, 2101, 'completed', 1, 1080, 1080, '2026-03-10 20:00:00', '2026-03-10 20:00:00', '2026-03-10 20:00:00'),
(8502, 1101, 6022, 2101, 'in_progress', 0, 420, 420, NULL, '2026-03-22 21:00:00', '2026-03-22 21:00:00'),
(8503, 1102, 6009, 2003, 'completed', 1, 960, 960, '2026-03-12 19:00:00', '2026-03-12 19:00:00', '2026-03-12 19:00:00'),
(8504, 1103, 6037, 2105, 'completed', 1, 900, 900, '2026-03-18 18:00:00', '2026-03-18 18:00:00', '2026-03-18 18:00:00'),
(8505, 1103, 6038, 2105, 'completed', 1, 0, 0, '2026-03-20 18:10:00', '2026-03-20 18:10:00', '2026-03-20 18:10:00'),
(8506, 1104, 6017, 2005, 'completed', 1, 1020, 1020, '2026-03-15 17:00:00', '2026-03-15 17:00:00', '2026-03-15 17:00:00'),
(8507, 1104, 6018, 2005, 'completed', 1, 0, 0, '2026-03-17 17:20:00', '2026-03-17 17:20:00', '2026-03-17 17:20:00'),
(8508, 1105, 6025, 2102, 'completed', 1, 930, 930, '2026-03-18 16:00:00', '2026-03-18 16:00:00', '2026-03-18 16:00:00'),
(8509, 1106, 6005, 2002, 'completed', 1, 870, 870, '2026-03-18 20:00:00', '2026-03-18 20:00:00', '2026-03-18 20:00:00'),
(8510, 1107, 6033, 2104, 'completed', 1, 960, 960, '2026-03-18 14:30:00', '2026-03-18 14:30:00', '2026-03-18 14:30:00'),
(8511, 1107, 6034, 2104, 'completed', 1, 0, 0, '2026-03-20 14:45:00', '2026-03-20 14:45:00', '2026-03-20 14:45:00'),
(8512, 1108, 6009, 2003, 'completed', 1, 980, 980, '2026-03-16 11:00:00', '2026-03-16 11:00:00', '2026-03-16 11:00:00'),
(8513, 1108, 6010, 2003, 'completed', 1, 0, 0, '2026-03-18 11:10:00', '2026-03-18 11:10:00', '2026-03-18 11:10:00'),
(8514, 1109, 6037, 2105, 'completed', 1, 940, 940, '2026-03-17 20:20:00', '2026-03-17 20:20:00', '2026-03-17 20:20:00'),
(8515, 1110, 6013, 2004, 'completed', 1, 900, 900, '2026-03-18 18:10:00', '2026-03-18 18:10:00', '2026-03-18 18:10:00'),
(8516, 1110, 6014, 2004, 'completed', 1, 0, 0, '2026-03-19 18:15:00', '2026-03-19 18:15:00', '2026-03-19 18:15:00');

INSERT IGNORE INTO `certificates` (
    `id`, `user_id`, `course_id`, `template_id`, `certificate_code`, `certificate_url`,
    `final_grade`, `total_hours`, `metadata`, `issued_at`
) VALUES
(9401, 1101, 2003, 9501, 'GDA-2026-0001', '/certificates/GDA-2026-0001', 96.00, 42.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"paid\"}', '2026-02-20 19:10:00'),
(9402, 1102, 2005, 9501, 'GDA-2026-0002', '/certificates/GDA-2026-0002', 94.00, 48.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"paid\"}', '2026-03-01 21:10:00'),
(9403, 1103, 2104, 9501, 'GDA-2026-0003', '/certificates/GDA-2026-0003', 91.00, 7.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"paid\"}', '2026-02-12 18:10:00'),
(9404, 1104, 2101, 9501, 'GDA-2026-0004', '/certificates/GDA-2026-0004', 93.00, 6.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"paid\"}', '2026-02-18 20:35:00'),
(9405, 1105, 2103, 9501, 'GDA-2026-0005', '/certificates/GDA-2026-0005', 90.00, 5.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"free\"}', '2026-02-15 15:05:00'),
(9406, 1106, 2001, 9501, 'GDA-2026-0006', '/certificates/GDA-2026-0006', 88.00, 18.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"free\"}', '2026-01-25 18:05:00'),
(9407, 1107, 2102, 9501, 'GDA-2026-0007', '/certificates/GDA-2026-0007', 95.00, 8.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"paid\"}', '2026-02-27 18:35:00'),
(9408, 1108, 2004, 9501, 'GDA-2026-0008', '/certificates/GDA-2026-0008', 89.00, 16.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"free\"}', '2026-01-30 16:35:00'),
(9409, 1109, 2002, 9501, 'GDA-2026-0009', '/certificates/GDA-2026-0009', 87.00, 24.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"paid\"}', '2026-02-14 19:05:00'),
(9410, 1110, 2103, 9501, 'GDA-2026-0010', '/certificates/GDA-2026-0010', 86.00, 5.0, '{\"issuer\":\"GameDev Academy Demo\",\"type\":\"free\"}', '2026-02-22 14:05:00');

INSERT IGNORE INTO `weekly_leaderboard` (`id`, `user_id`, `week_start`, `xp_earned`, `lessons_completed`) VALUES
(9901, 1101, '2026-03-23', 1240, 12),
(9902, 1102, '2026-03-23', 1180, 11),
(9903, 1103, '2026-03-23', 1110, 10),
(9904, 1104, '2026-03-23', 980, 9),
(9905, 1105, '2026-03-23', 910, 8),
(9906, 1106, '2026-03-23', 840, 7),
(9907, 1107, '2026-03-23', 790, 7),
(9908, 1108, '2026-03-23', 650, 6),
(9909, 1109, '2026-03-23', 590, 5),
(9910, 1110, '2026-03-23', 470, 4);

INSERT IGNORE INTO `xp_history` (
    `id`, `user_id`, `xp_amount`, `action_type`, `description`, `reference_id`, `reference_type`, `created_at`
) VALUES
(9951, 1101, 8200, 'course_completed', 'Acumulado de cursos concluidos e masterclasses.', 2003, 'course', '2026-03-23 08:00:00'),
(9952, 1102, 7600, 'course_completed', 'Acumulado de cursos longos concluidos.', 2005, 'course', '2026-03-23 08:00:00'),
(9953, 1103, 7100, 'subscription_progress', 'XP ganho com cursos do plano ativo.', 9701, 'subscription', '2026-03-23 08:00:00'),
(9954, 1104, 6400, 'course_completed', 'XP de cursos pagos e masterclasses.', 2101, 'course', '2026-03-23 08:00:00'),
(9955, 1105, 5900, 'course_completed', 'XP acumulado em arte e assinatura.', 9702, 'subscription', '2026-03-23 08:00:00'),
(9956, 1106, 5200, 'course_completed', 'XP de trilhas gratuitas e pagas.', 2005, 'course', '2026-03-23 08:00:00'),
(9957, 1107, 4700, 'course_completed', 'XP acumulado em Unity e masterclasses.', 2102, 'course', '2026-03-23 08:00:00'),
(9958, 1108, 3900, 'subscription_progress', 'XP do plano anual ativo.', 9703, 'subscription', '2026-03-23 08:00:00'),
(9959, 1109, 3400, 'course_completed', 'XP de compras avulsas e progresso recente.', 2105, 'course', '2026-03-23 08:00:00'),
(9960, 1110, 2800, 'course_completed', 'XP de cursos gratuitos e trilha inicial.', 2103, 'course', '2026-03-23 08:00:00');

INSERT IGNORE INTO `financial_expenses` (
    `id`, `title`, `category`, `amount`, `currency`, `expense_date`, `status`,
    `vendor_name`, `notes`, `created_by`
) VALUES
(9801, 'Hospedagem da plataforma', 'infraestrutura', 129.90, 'BRL', '2026-03-02', 'paid', 'Cloud Demo', 'Custo mensal de hospedagem e banco.', 1001),
(9802, 'Campanha de captacao', 'marketing', 950.00, 'BRL', '2026-03-05', 'approved', 'Ads Studio', 'Midia para aquisicao de novos alunos.', 1001),
(9803, 'Ferramentas de colaboracao', 'ferramentas', 299.90, 'BRL', '2026-03-08', 'paid', 'Workspace Tools', 'Assinaturas de produtividade e design.', 1001),
(9804, 'Atendimento ao aluno', 'suporte', 180.00, 'BRL', '2026-03-11', 'planned', 'Help Desk', 'Custo previsto para suporte ao aluno.', 1001),
(9805, 'Tributos do periodo', 'tributos', 640.00, 'BRL', '2026-03-14', 'paid', 'Receita Demo', 'Tributos simulados sobre a operacao.', 1001),
(9806, 'Equipe de conteudo', 'pessoal', 1200.00, 'BRL', '2026-03-18', 'approved', 'Instrutores Parceiros', 'Reserva de pagamento para criadores.', 1001);

INSERT IGNORE INTO `instructor_payouts` (
    `id`, `instructor_id`, `amount`, `currency`, `period_start`, `period_end`, `total_sales`,
    `gross_amount`, `platform_fee`, `payment_method`, `payment_details`, `status`, `paid_at`, `notes`
) VALUES
(9851, 1002, 149.94, 'BRL', '2026-02-01', '2026-02-29', 3, 249.90, 99.96, 'pix', '{\"bank\":\"demo-bank\",\"account\":\"1002\"}', 'completed', '2026-03-05 10:00:00', 'Repasse de cursos Phaser e publicacao.'),
(9852, 1004, 179.88, 'BRL', '2026-02-01', '2026-02-29', 2, 299.80, 119.92, 'pix', '{\"bank\":\"demo-bank\",\"account\":\"1004\"}', 'pending', NULL, 'Repasse previsto para Unity e IA para NPCs.'),
(9853, 1006, 153.93, 'BRL', '2026-02-01', '2026-02-29', 3, 219.90, 65.97, 'pix', '{\"bank\":\"demo-bank\",\"account\":\"1006\"}', 'processing', NULL, 'Repasse de game design e monetizacao.');

SET FOREIGN_KEY_CHECKS = 1;
