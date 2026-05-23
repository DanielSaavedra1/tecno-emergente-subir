import { Head, Link, usePage } from '@inertiajs/react';
import { login, register } from '@/routes';
import { index as workspace } from '@/routes/workspace';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Tecno Emergente" />

            <main className="h-dvh overflow-hidden bg-[#f6f0e8] text-[#251f1a] dark:bg-[#120f0d] dark:text-[#f7efe6]">
                <section className="relative mx-auto flex h-full w-full max-w-7xl flex-col px-6 py-6 sm:px-10 lg:px-12">
                    <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(140,61,61,0.18),transparent_32%),radial-gradient(circle_at_80%_10%,rgba(222,177,100,0.2),transparent_28%),linear-gradient(135deg,transparent_0_48%,rgba(37,31,26,0.08)_48%_52%,transparent_52%)]" />

                    <header className="relative z-10 flex items-center justify-between gap-4">
                        <div>
                            <p className="text-xs font-black tracking-[0.34em] uppercase">
                                Tecno Emergente
                            </p>
                        </div>

                        <nav className="flex items-center gap-3 text-sm font-bold">
                            {auth.user ? (
                                <Link
                                    href={workspace()}
                                    className="rounded-full bg-[#251f1a] px-5 py-2 text-[#f8efe6] transition hover:bg-[#8c3d3d] dark:bg-[#f8efe6] dark:text-[#251f1a] dark:hover:bg-[#deb164]"
                                >
                                    Ir al workspace
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="rounded-full border border-[#251f1a]/20 px-5 py-2 transition hover:border-[#8c3d3d] hover:text-[#8c3d3d] dark:border-[#f8efe6]/25 dark:hover:border-[#deb164] dark:hover:text-[#deb164]"
                                    >
                                        Ingresar
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="rounded-full bg-[#251f1a] px-5 py-2 text-[#f8efe6] transition hover:bg-[#8c3d3d] dark:bg-[#f8efe6] dark:text-[#251f1a] dark:hover:bg-[#deb164]"
                                        >
                                            Crear cuenta
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </header>

                    <div className="relative z-10 grid flex-1 items-center gap-12 py-16 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="max-w-3xl">
                            <p className="mb-5 inline-flex rounded-full border border-[#8c3d3d]/30 bg-white/55 px-4 py-2 text-xs font-black tracking-[0.28em] text-[#8c3d3d] uppercase shadow-sm dark:bg-white/5 dark:text-[#deb164]">
                                Laboratorio de programacion
                            </p>

                            <h1 className="text-5xl leading-[0.95] font-black tracking-[-0.06em] text-balance sm:text-7xl lg:text-8xl">
                                Aprende Python resolviendo problemas reales.
                            </h1>

                            <p className="mt-7 max-w-2xl text-lg leading-8 text-[#5f544c] sm:text-xl dark:text-[#d8cabc]">
                                Practica funciones, condicionales y bucles con
                                un workspace que ejecuta tus soluciones y
                                compara los resultados caso por caso.
                            </p>

                            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
                                <Link
                                    href={auth.user ? workspace() : login()}
                                    className="inline-flex items-center justify-center rounded-2xl bg-[#8c3d3d] px-6 py-4 text-sm font-black tracking-[0.16em] text-white uppercase shadow-[0_18px_45px_rgba(140,61,61,0.28)] transition hover:-translate-y-0.5 hover:bg-[#743131]"
                                >
                                    Abrir workspace
                                </Link>
                                {!auth.user && canRegister && (
                                    <Link
                                        href={register()}
                                        className="inline-flex items-center justify-center rounded-2xl border border-[#251f1a]/20 bg-white/45 px-6 py-4 text-sm font-black tracking-[0.16em] uppercase transition hover:-translate-y-0.5 hover:border-[#8c3d3d] dark:border-[#f8efe6]/20 dark:bg-white/5"
                                    >
                                        Empezar gratis
                                    </Link>
                                )}
                            </div>
                        </div>

                        <aside className="rounded-[2rem] border border-[#251f1a]/10 bg-[#fffaf2]/80 p-5 shadow-[0_30px_80px_rgba(37,31,26,0.16)] backdrop-blur dark:border-white/10 dark:bg-white/5">
                            <div className="rounded-[1.5rem] bg-[#251f1a] p-5 text-[#f8efe6] dark:bg-[#f8efe6] dark:text-[#251f1a]">
                                <div className="mb-5 flex items-center justify-between border-b border-white/15 pb-4 text-xs font-black tracking-[0.24em] uppercase dark:border-[#251f1a]/15">
                                    <span>solucion.py</span>
                                    <span>Python</span>
                                </div>
                                <pre className="overflow-x-auto text-sm leading-7">
                                    {`def sumar_hasta(n):
    total = 0
    for numero in range(1, n + 1):
        total += numero
    return total`}
                                </pre>
                            </div>

                            <div className="mt-5 grid gap-3 sm:grid-cols-3">
                                {[
                                    ['01', 'Lee el enunciado'],
                                    ['02', 'Escribe tu funcion'],
                                    ['03', 'Compila y mejora'],
                                ].map(([number, label]) => (
                                    <div
                                        key={number}
                                        className="rounded-2xl border border-[#251f1a]/10 bg-white/65 p-4 dark:border-white/10 dark:bg-white/5"
                                    >
                                        <p className="text-lg font-black text-[#8c3d3d] dark:text-[#deb164]">
                                            {number}
                                        </p>
                                        <p className="mt-2 text-sm leading-5 font-bold">
                                            {label}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </aside>
                    </div>
                </section>
            </main>
        </>
    );
}
