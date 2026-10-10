#!/bin/bash

# ============================================================
# Git Helper - Script simples para versionamento do projeto
# Autor: uso local
# ============================================================

set -e

PROJECT_DIR="$(pwd)"
DEFAULT_BRANCH="main"

GREEN="\e[32m"
RED="\e[31m"
YELLOW="\e[33m"
BLUE="\e[34m"
RESET="\e[0m"

pause() {
    echo ""
    read -p "Pressione ENTER para continuar..."
}

success() {
    echo -e "${GREEN}$1${RESET}"
}

error() {
    echo -e "${RED}$1${RESET}"
}

warning() {
    echo -e "${YELLOW}$1${RESET}"
}

info() {
    echo -e "${BLUE}$1${RESET}"
}

check_git_installed() {
    if ! command -v git >/dev/null 2>&1; then
        error "Git não está instalado."

        read -p "Deseja instalar agora? [s/n]: " install_git

        if [[ "$install_git" == "s" || "$install_git" == "S" ]]; then
            sudo apt update
            sudo apt install git -y
        else
            error "Instale o Git antes de continuar."
            exit 1
        fi
    fi
}

configure_git_user() {
    current_name="$(git config --global user.name || true)"
    current_email="$(git config --global user.email || true)"

    if [[ -z "$current_name" ]]; then
        read -p "Digite seu nome para o Git: " git_name
        git config --global user.name "$git_name"
        success "Nome configurado."
    fi

    if [[ -z "$current_email" ]]; then
        read -p "Digite seu e-mail do Git: " git_email
        git config --global user.email "$git_email"
        success "E-mail configurado."
    fi
}

init_git_if_needed() {
    if [[ ! -d ".git" ]]; then
        warning "Este projeto ainda não possui Git configurado."

        read -p "Deseja iniciar o Git nesta pasta? [s/n]: " init_git

        if [[ "$init_git" == "s" || "$init_git" == "S" ]]; then
            git init
            git branch -M "$DEFAULT_BRANCH"

            success "Git iniciado na branch $DEFAULT_BRANCH."

            create_gitignore_if_needed

            git add .
            git commit -m "commit inicial" || true

            read -p "Deseja configurar um repositório remoto agora? [s/n]: " add_remote

            if [[ "$add_remote" == "s" || "$add_remote" == "S" ]]; then
                setup_remote
            fi
        else
            error "Git não configurado. Encerrando."
            exit 1
        fi
    fi
}

create_gitignore_if_needed() {
    if [[ ! -f ".gitignore" ]]; then
        cat > .gitignore << 'EOF'
# Dependências
node_modules/
vendor/

# Ambiente
.env
.env.*
!.env.example

# Logs
*.log
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# Sistema
.DS_Store
Thumbs.db

# IDEs
.idea/
.vscode/

# Uploads e arquivos temporários
tmp/
temp/
cache/
uploads/
arquivos/

# Build
dist/
build/

# Credenciais
credentials.json
token.json
sessions/
session/
*.pem
*.key
EOF

        success ".gitignore criado com padrões básicos."
    fi
}

setup_remote() {
    current_remote="$(git remote get-url origin 2>/dev/null || true)"

    if [[ -n "$current_remote" ]]; then
        warning "Remote atual:"
        echo "$current_remote"

        read -p "Deseja trocar o remote? [s/n]: " change_remote

        if [[ "$change_remote" != "s" && "$change_remote" != "S" ]]; then
            return
        fi

        git remote remove origin
    fi

    echo ""
    read -p "Cole a URL do repositório remoto: " remote_url

    if [[ -z "$remote_url" ]]; then
        error "URL vazia. Remote não configurado."
        return
    fi

    git remote add origin "$remote_url"
    success "Remote origin configurado."
}

show_status() {
    info "Status do projeto:"
    echo ""
    git status
    pause
}

show_diff() {
    info "Diferenças não commitadas:"
    echo ""
    git diff
    pause
}

show_log() {
    info "Últimos commits:"
    echo ""
    git log --oneline --decorate --graph -10
    pause
}

commit_changes() {
    echo ""
    git status --short

    echo ""
    read -p "Digite a mensagem do commit: " commit_message

    if [[ -z "$commit_message" ]]; then
        error "Mensagem vazia. Commit cancelado."
        pause
        return
    fi

    git add .

    if git diff --cached --quiet; then
        warning "Nenhuma alteração para commitar."
        pause
        return
    fi

    git commit -m "$commit_message"
    success "Commit criado com sucesso."
    pause
}

push_changes() {
    current_branch="$(git branch --show-current)"

    if [[ -z "$current_branch" ]]; then
        current_branch="$DEFAULT_BRANCH"
    fi

    remote_url="$(git remote get-url origin 2>/dev/null || true)"

    if [[ -z "$remote_url" ]]; then
        warning "Nenhum remote configurado."
        read -p "Deseja configurar agora? [s/n]: " configure_now

        if [[ "$configure_now" == "s" || "$configure_now" == "S" ]]; then
            setup_remote
        else
            error "Push cancelado."
            pause
            return
        fi
    fi

    info "Enviando para origin/$current_branch..."
    git push -u origin "$current_branch"

    success "Push realizado com sucesso."
    pause
}

commit_and_push() {
    commit_changes
    push_changes
}

pull_changes() {
    current_branch="$(git branch --show-current)"

    if [[ -z "$current_branch" ]]; then
        current_branch="$DEFAULT_BRANCH"
    fi

    info "Puxando alterações de origin/$current_branch..."
    git pull origin "$current_branch"

    success "Pull realizado com sucesso."
    pause
}

backup_before_ai() {
    timestamp="$(date +%Y%m%d_%H%M%S)"
    branch="$(git branch --show-current)"

    if [[ -z "$branch" ]]; then
        branch="$DEFAULT_BRANCH"
    fi

    git add .

    if git diff --cached --quiet; then
        warning "Nenhuma alteração nova para salvar."
    else
        git commit -m "backup antes da IA - $timestamp"
        success "Backup commitado."
    fi

    tag_name="backup-ia-$timestamp"
    git tag "$tag_name"

    success "Tag de backup criada: $tag_name"
    warning "Você pode voltar para esse ponto usando:"
    echo "git reset --hard $tag_name"

    pause
}

revert_uncommitted() {
    warning "Isso vai apagar alterações NÃO commitadas."
    warning "Arquivos modificados voltarão para o último commit."
    echo ""

    read -p "Tem certeza? Digite SIM para confirmar: " confirm

    if [[ "$confirm" == "SIM" ]]; then
        git restore .
        git clean -fd
        success "Alterações não commitadas revertidas."
    else
        warning "Operação cancelada."
    fi

    pause
}

revert_last_commit_keep_changes() {
    warning "Isso desfaz o último commit, mas mantém os arquivos modificados."
    echo ""

    read -p "Tem certeza? Digite SIM para confirmar: " confirm

    if [[ "$confirm" == "SIM" ]]; then
        git reset --soft HEAD~1
        success "Último commit desfeito, alterações mantidas."
    else
        warning "Operação cancelada."
    fi

    pause
}

revert_last_commit_delete_changes() {
    warning "PERIGO: isso apaga o último commit e suas alterações."
    echo ""

    read -p "Tem certeza? Digite SIM para confirmar: " confirm

    if [[ "$confirm" == "SIM" ]]; then
        git reset --hard HEAD~1
        success "Último commit removido com alterações apagadas."
    else
        warning "Operação cancelada."
    fi

    pause
}

fix_permissions() {
    warning "Use isso apenas se arquivos ficaram com permissão errada."
    warning "Isso vai alterar o dono dos arquivos para o usuário atual."
    echo ""

    read -p "Tem certeza? Digite SIM para confirmar: " confirm

    if [[ "$confirm" == "SIM" ]]; then
        sudo chown -R "$USER":"$USER" "$PROJECT_DIR"
        success "Permissões corrigidas."
    else
        warning "Operação cancelada."
    fi

    pause
}

environment_setup() {
    check_git_installed
    configure_git_user
    init_git_if_needed
    create_gitignore_if_needed

    success "Ambiente Git verificado/configurado."
    pause
}

menu() {
    clear
    echo "=================================================="
    echo " Git Helper"
    echo " Projeto: $PROJECT_DIR"
    echo "=================================================="
    echo ""
    echo "1) Configurar/verificar ambiente Git"
    echo "2) Ver status"
    echo "3) Ver diferenças"
    echo "4) Criar commit"
    echo "5) Enviar para o Git remoto"
    echo "6) Criar commit e enviar"
    echo "7) Puxar alterações do remoto"
    echo "8) Criar backup antes da IA"
    echo "9) Ver últimos commits"
    echo "10) Configurar/trocar remote"
    echo "11) Reverter alterações não commitadas"
    echo "12) Desfazer último commit mantendo alterações"
    echo "13) Desfazer último commit apagando alterações"
    echo "14) Corrigir permissões com sudo"
    echo "0) Sair"
    echo ""
    read -p "Escolha uma opção: " option

    case "$option" in
        1) environment_setup ;;
        2) show_status ;;
        3) show_diff ;;
        4) commit_changes ;;
        5) push_changes ;;
        6) commit_and_push ;;
        7) pull_changes ;;
        8) backup_before_ai ;;
        9) show_log ;;
        10) setup_remote; pause ;;
        11) revert_uncommitted ;;
        12) revert_last_commit_keep_changes ;;
        13) revert_last_commit_delete_changes ;;
        14) fix_permissions ;;
        0) exit 0 ;;
        *) error "Opção inválida."; pause ;;
    esac
}

check_git_installed
configure_git_user
init_git_if_needed

while true; do
    menu
done