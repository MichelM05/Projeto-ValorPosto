# Projeto: Combustível CWB-SJP

## Contexto e objetivo
Construir uma aplicação web em PHP que ajude motoristas de Curitiba e São José dos Pinhais (PR) a decidir onde e com qual combustível abastecer. Além de listar preços por posto, a aplicação calcula se compensa ir até um posto mais barato, considerando o custo do desvio, e compara etanol e gasolina com base no consumo real do carro do usuário.

O projeto será publicado no GitHub como peça de portfólio para vagas de desenvolvimento PHP, inclusive no exterior. Por isso: código, commits, nomes de classes, README e documentação da API em inglês; interface para o usuário em português (pt-BR).

## Escopo geográfico
Somente os municípios de Curitiba e São José dos Pinhais. Todo filtro, importação e consulta deve ser restrito a esses dois municípios, com a lista de municípios permitidos em configuração (não fixa no código), para facilitar expansão futura.

## Stack
- PHP 8.3 ou superior, Laravel (versão estável mais recente compatível)
- PostgreSQL 16 ou superior
- Front-end: Blade + Alpine.js, mapa com Leaflet e tiles do OpenStreetMap (sem build complexo de SPA)
- Testes: Pest ou PHPUnit; análise estática: Larastan (PHPStan); estilo: Laravel Pint
- Docker Compose (app, nginx, postgres, scheduler) para subir tudo com um único comando
- GitHub Actions: lint, análise estática e testes a cada push

## Fonte de dados
- Fonte principal: arquivo semanal "Preços por posto revendedor (combustíveis automotivos e GLP P13)" do Levantamento de Preços de Combustíveis da ANP, em formato XLSX, publicado na página "Levantamento de Preços de Combustíveis (últimas semanas pesquisadas)" do gov.br/anp. Cada arquivo cobre uma semana e traz todos os municípios do país.
- Fonte histórica (Fase 5): série histórica de preços de combustíveis por posto, nos dados abertos da ANP.
- O importador recebe o arquivo por caminho local ou URL (comando Artisan com argumento e variável de ambiente para o diretório padrão). Não depender do padrão do nome do arquivo e não fazer scraping da página HTML no MVP.
- Ler o XLSX em streaming (por exemplo, com OpenSpout), sem carregar o arquivo inteiro na memória; filtrar durante a leitura apenas Curitiba e São José dos Pinhais e apenas os combustíveis de veículos (gasolina comum, gasolina aditivada, etanol, diesel S10, GNV), descartando GLP.
- Não invente nomes de colunas. Mapeie por nome do cabeçalho em arquivo de configuração e aguarde a amostra real que vou fornecer em storage/samples. Normalizar acentos e caixa ao comparar nomes de município e de combustível.
- Os preços refletem a data de coleta da ANP (semanal e por amostragem). Exibir sempre a data da coleta e a fonte, sem sugerir preço em tempo real.

## Modelo de domínio (mínimo)
- Station: identificador do CNPJ, nome, bandeira, endereço completo, município, latitude e longitude (anuláveis até o geocoding)
- FuelPrice: posto, tipo de combustível, valor por litro, data da coleta
- Import: registro de cada importação (arquivo/origem, data, linhas lidas, inseridas, ignoradas, erros)
Regras: importação idempotente (reimportar o mesmo arquivo não duplica registros); descartar linhas de outros municípios; normalizar nomes de combustível e de bairro.

## Geocoding
Cada posto é geocodificado uma única vez e o resultado é salvo. Usar o Nominatim (OpenStreetMap) atrás de uma interface, para permitir trocar de provedor. Respeitar a política de uso do serviço (limite de 1 requisição por segundo, User-Agent identificado, cache). Postos que falharem ficam marcados como "sem coordenadas" e continuam visíveis na listagem, apenas fora do mapa e dos cálculos de distância.

## Funcionalidades do MVP
1. Listagem e mapa dos postos por município, com o preço mais recente por combustível, filtros por combustível e bandeira, e ordenação por preço.
2. Calculadora "Vale a pena o desvio?": o usuário informa sua localização de partida (geolocalização do navegador ou bairro), o consumo do carro em km/l, os litros a abastecer e o posto de referência (o mais próximo ou um escolhido). A aplicação calcula, para cada posto candidato: economia bruta = (preço de referência − preço do posto) × litros; custo do desvio = km adicionais × (1 / consumo) × preço do combustível; economia líquida = economia bruta − custo do desvio. Distância no MVP: linha reta (Haversine) multiplicada por um fator de correção configurável (padrão 1,3), sempre sinalizada na interface como estimativa. Deixar a estimativa de distância atrás de uma interface, para trocar depois por um serviço de rotas.
3. Calculadora etanol x gasolina: recebe o consumo do carro com cada combustível (km/l) e os preços atuais do município; indica o mais vantajoso pela relação (preço do etanol ÷ preço da gasolina) comparada com (consumo com etanol ÷ consumo com gasolina). Se o usuário não informar consumos, usar o parâmetro geral de 70% e avisar que é uma média.
4. Histórico: gráfico semanal do preço médio e do menor preço por município e combustível.
5. API pública somente de leitura em JSON (postos, preços, histórico) com documentação OpenAPI/Swagger e limite de requisições.

Fora do escopo do MVP: contas de usuário, alertas por e-mail, roteamento real, outros municípios, aplicativo móvel.

## Privacidade
Não persistir a localização do usuário nem os dados do carro; processar apenas por requisição (ou no navegador). Incluir no rodapé a fonte dos dados e um aviso de que os valores são de referência.

## Qualidade
- Lógica de cálculo isolada em classes de serviço puras, cobertas por testes unitários com casos de borda (desvio maior que a economia, posto sem coordenadas, preços iguais, consumo zero ou inválido).
- Teste de importação com um arquivo pequeno de fixture (várias cidades, linhas inválidas, reimportação).
- Testes de feature para os endpoints da API.
- Tratamento de erros e logs claros na importação; nenhum segredo no repositório (usar .env.example).

## Ordem de trabalho (execute por fase e rode os testes ao final de cada uma)
Fase 0: esqueleto do projeto, Docker Compose, CI, Pint e Larastan configurados.
Fase 1: modelos, migrations e importador da ANP, com testes.
Fase 2: geocoding com cache e comando agendado semanal de importação.
Fase 3: API de leitura, listagem e mapa.
Fase 4: calculadoras (desvio e etanol x gasolina) com testes unitários.
Fase 5: histórico com gráfico.
Fase 6: README em inglês (problema, decisões de arquitetura, diagrama simples, como subir com um comando, limitações dos dados), documentação OpenAPI e capturas de tela.

## Restrições
- Não adicione dependências sem necessidade e justifique cada uma no README.
- Não implemente nada fora do escopo acima.
- Se algo estiver ambíguo ou faltar um dado (por exemplo, o arquivo da ANP), pergunte antes de assumir.
- Mantenha commits pequenos, com mensagens descritivas em inglês.