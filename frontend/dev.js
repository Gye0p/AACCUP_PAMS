import { spawn } from 'node:child_process'
import { fileURLToPath } from 'node:url'
import path from 'node:path'

const frontendDir = path.dirname(fileURLToPath(import.meta.url))
const backendDir = path.resolve(frontendDir, '..', 'backend')

const sleep = (milliseconds) =>
  new Promise(resolve => setTimeout(resolve, milliseconds))

async function isApiRunning() {
  try {
    const response = await fetch('http://127.0.0.1:8080/api/docs', {
      signal: AbortSignal.timeout(1000),
    })
    return response.status < 500
  } catch {
    return false
  }
}

const children = []

async function startBackend() {
  if (await isApiRunning()) {
    console.log('Symfony API is already running at http://127.0.0.1:8080')
    return
  }

  console.log('Starting Symfony API at http://127.0.0.1:8080...')
  const backend = spawn(
    'php',
    ['-S', '127.0.0.1:8080', '-t', 'public', 'public\\index.php'],
    {
      cwd: backendDir,
      stdio: 'inherit',
    },
  )
  children.push(backend)

  for (let attempt = 0; attempt < 30; attempt += 1) {
    await sleep(500)
    if (await isApiRunning()) {
      console.log('Symfony API ready.')
      return
    }
  }

  throw new Error('Symfony API did not start on http://127.0.0.1:8080')
}

const vite = spawn(
  process.execPath,
  [path.join(frontendDir, 'node_modules', 'vite', 'bin', 'vite.js'), '--host', '127.0.0.1'],
  {
    cwd: frontendDir,
    stdio: 'inherit',
  },
)
children.push(vite)

try {
  await startBackend()
} catch (error) {
  console.error(error.message)
  vite.kill('SIGTERM')
  process.exitCode = 1
}

const shutdown = () => {
  for (const child of children) {
    child.kill('SIGTERM')
  }
}

process.once('SIGINT', shutdown)
process.once('SIGTERM', shutdown)
