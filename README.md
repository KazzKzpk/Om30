## Contato
#### Gabriel Morgado
#### kazzxd1@gmail.com
#### (11) 97723-5000


## Configuração
Não é necessário configurar.

## Subindo o projeto
Necessário apenas subir os containers no docker.

`docker-compose up -d --build`

Acesso a linha de comando.

`docker-compose exec app bash`

Instalar dependências do Laravel.

`composer install`

## Containers
### PHP
Rodando a versão 8.1-fpm.

Adicionais:
- Composer
- Redis

### NGINX

### Postgress

### PGAdmin
Rodando a PGAdmin versão 4 web para acesso ao banco de dados Postgress.

### Redis

## Rodando o projeto

Necessário apenas fazer a migration, acessando a linha de comando.

`php artisan migrate`

# Resumo

Criado CRUD para cadastro de usuários e endereços.

Tabela de usuários e endereços são separadas, sendo montadas nas respostas de API (excesso acesso direto a API de endereço).

Existe um sistema de cache de endereços (Redis), utilizando a API de CEP.

## Detalhes
### Geral
#### Providers/AppServiceProvider:
- Adicionado a função **onlyNumbers** (retorna apenas numeros), extendendo a classe Str.
- Adicionado a função **matchCode** (retorna string em minúsculo, ASCII e remove espaços/hífens), extendendo a classe Str.
- Adicionado a validação **cpf** (valida inputs nativos do tipo CPF), extendendo a classe Validator.
- Adicionado a validação **cns** (valida inputs nativos do tipo CNS), extendendo a classe Validator.

#### Exceptions/Handler
- Adicionado hook de render para retornos em **api/*** retornar json's com status **500**, em vez do padrão **404**.

#### Routes/API
- Rotas de usuário utilizando resource.
- Rotas de endereço utilizando get.
- Rotas de endereço de usuário utilizando resource.

### Models
#### User
- Adicionado a função (não estática) **updateMatchCode**, que atualiza o matchcode de um usuário diretamente na model.
- Adicionado a função **find**, retornando usuário.
- Adicionado a função **getPaginated**, retornando usuários paginados.
- Adicionado a função **getByCPF**, retornando usuário pela pesquisa por CPF.
- Adicionado a função **getByCNS**, retornando usuário pela pesquisa por CNS.
- Adicionado a função **isRegisteredCPF**, retornando validação do CPF já ter sido registrado em outro usuário.
- Adicionado a função **isRegisteredCNS**, retornando validação do CNS já ter sido registrado em outro usuário.
- Adicionado a função **getByMatchCode**, retornando usuário pela pesquisa por nome (utilizando matchcode com wildcard, sendo possível pesquisa por pedaços do nome).

#### UserAddress

### API

Arquivo **insomnia.json** na pasta root do projeto.

#### Usuário
- Função **index**, **endpoint localhost/user (get)**, retorna usuários paginados.
- Função **store**, endpoint **localhost/user (post)**, enviando parâmetros, cria e retorna usuário.
- Função **search**, endpoint **localhost/user (get)**, enviando parâmetros, retorna usuário pesquisado (CPF/CNS/MatchCode).
- Função **show**, endpoint **local/user/{id} (get)**, retorna usuário pelo id.
- Função **update**, endpoint **local/user/{id} (patch)**, enviando parâmetros, atualiza e retorna usuário pelo id.
- Função **destroy**, endpoint **local/user/{id} (delete)**, deleta (soft delete) usuário.

#### Endereço
- Função **show**, endpoint **local/user/{id} (get)**, enviando parâmetros, retorna endereço pesquisado (CPF).

#### Usuário/Endereço
- Função **store**, endpoint **localhost/user/address (post)**, enviando parâmetros, cria e retorna endereço.
- Função **update**, endpoint **local/user/address/{id} (patch)**, enviando parâmetros, atualiza e retorna endereço pelo id.

## Importação de usuários

Para importar usuários, é necessário disparar o Job pelo comando:

`php artisan app:user-import import.csv --name=1 --name_mother=2 --birth=3 --cpf=5 --cns=7 --cep=11 --number=12 --number_ex=13 --now=true`

O primeiro parâmetro (**import.csv**) é o nome do arquivo.

Os próximos parâmetros (opções), são referentes a qual **coluna do CSV** tem o determinado dado.
Imaginando que os CSV's nem sempre contem somente os dados a serem importados, é possível selecionar qual coluna é responsável por cada dado.

O último parâmetro (opção **-now**) é referente a execução, se o parâmetro for valor true, o comando irá executar diretamente a importação, sem jogar na Queue.

Para executar na Queue:

`php artisan queue:work`

`php artisan app:user-import import.csv --name=1 --name_mother=2 --birth=3 --cpf=5 --cns=7 --cep=11 --number=12 --number_ex=13`

#### Detalhes
- A importação executa as mesmas funções dos controladores.
- Todas validações e retornos de API funcionam, mesmo dentro de uma Job/Commando.
- Em vez de retornos em JSON, será impresso em texto na linha de comando.

Obrigado! :)
