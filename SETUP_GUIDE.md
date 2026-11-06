# OpenCode + Laravel: learning-mentor

## Instalacion rapida

1. Instala las cinco Skills globales desde PowerShell en la raiz del repositorio o cualquier carpeta:

```powershell
npx skills add OthmanAdi/planning-with-files --skill planning-with-files -g -a opencode -y
npx skills add addyosmani/agent-skills --skill context-engineering -g -a opencode -y
npx skills add obra/superpowers --skill systematic-debugging --skill using-git-worktrees --skill writing-plans -g -a opencode -y
```

2. Copia `.opencode/skills/learning-mentor/SKILL.md` a la MISMA ruta dentro del worktree de tu proyecto Laravel.
3. Agrega el contenido de `AGENTS_SNIPPET.md` al `AGENTS.md` existente, sin reemplazar sus reglas actuales.
4. Reinicia OpenCode y solicita: `Carga la skill learning-mentor y confirma sus reglas sin modificar archivos.`
5. Trabaja en ramas `feature/*` y worktrees. No fusionar los cambios de la configuracion a `main` sin revision.

**Importante:** Planning with Files tiene un plugin nativo opcional. Se omite inicialmente porque su gate de finalizacion podria reabrir la sesion al interpretar los ejercicios del estudiante como tareas del agente.

Fuentes: https://docs.opencode.ai/docs/skills/ ; https://github.com/OthmanAdi/planning-with-files/blob/master/docs/opencode.md ; https://github.com/obra/superpowers ; https://github.com/addyosmani/agent-skills
