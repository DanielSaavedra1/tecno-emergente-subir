<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\LearningLevel;
use App\Models\LearningTopic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LearningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentLevelSlugs = [];

        foreach ($this->curriculum() as $levelAttributes) {
            $currentLevelSlugs[] = $levelAttributes['slug'];

            $level = LearningLevel::query()->updateOrCreate(
                ['position' => $levelAttributes['position']],
                [
                    'name' => $levelAttributes['name'],
                    'title' => $levelAttributes['title'],
                    'description' => $levelAttributes['description'],
                    'slug' => $levelAttributes['slug'],
                    'is_active' => true,
                ],
            );

            $currentTopicSlugs = [];

            foreach ($levelAttributes['topics'] as $topicAttributes) {
                $currentTopicSlugs[] = $topicAttributes['slug'];

                $topic = LearningTopic::query()->updateOrCreate(
                    [
                        'learning_level_id' => $level->id,
                        'slug' => $topicAttributes['slug'],
                    ],
                    [
                        'name' => $topicAttributes['name'],
                        'description' => $topicAttributes['description'],
                        'position' => $topicAttributes['position'],
                        'is_active' => true,
                    ],
                );

                $currentExerciseSlugs = [];

                foreach ($topicAttributes['exercises'] as $exerciseAttributes) {
                    $exerciseSlug = $exerciseAttributes['slug'] ?? Str::slug($exerciseAttributes['title']);
                    $currentExerciseSlugs[] = $exerciseSlug;

                    Exercise::query()->updateOrCreate(
                        [
                            'slug' => $exerciseSlug,
                        ],
                        [
                            'learning_topic_id' => $topic->id,
                            'number' => $exerciseAttributes['number'],
                            'title' => $exerciseAttributes['title'],
                            'description' => $exerciseAttributes['description'],
                            'exercise_type' => $exerciseAttributes['exercise_type'],
                            'function_name' => $exerciseAttributes['function_name'],
                            'input_description' => $exerciseAttributes['input_description'],
                            'output_description' => $exerciseAttributes['output_description'],
                            'examples' => $exerciseAttributes['examples'],
                            'considerations' => $exerciseAttributes['considerations'],
                            'starter_code' => $exerciseAttributes['starter_code'],
                            'test_cases' => $exerciseAttributes['test_cases'],
                            'difficulty' => $exerciseAttributes['difficulty'],
                            'position' => $exerciseAttributes['position'],
                            'is_active' => true,
                        ],
                    );
                }

                Exercise::query()
                    ->where('learning_topic_id', $topic->id)
                    ->whereNotIn('slug', $currentExerciseSlugs)
                    ->update(['is_active' => false]);
            }

            LearningTopic::query()
                ->where('learning_level_id', $level->id)
                ->whereNotIn('slug', $currentTopicSlugs)
                ->update(['is_active' => false]);

            Exercise::query()
                ->whereHas('topic', fn ($query) => $query
                    ->where('learning_level_id', $level->id)
                    ->whereNotIn('slug', $currentTopicSlugs))
                ->update(['is_active' => false]);
        }

        LearningLevel::query()
            ->whereNotIn('slug', $currentLevelSlugs)
            ->update(['is_active' => false]);

        LearningTopic::query()
            ->whereHas('level', fn ($query) => $query->whereNotIn('slug', $currentLevelSlugs))
            ->update(['is_active' => false]);

        Exercise::query()
            ->whereHas('topic.level', fn ($query) => $query->whereNotIn('slug', $currentLevelSlugs))
            ->update(['is_active' => false]);
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     slug: string,
     *     title: string,
     *     description: string,
     *     position: int,
     *     topics: array<int, array{
     *         name: string,
     *         slug: string,
     *         description: string,
     *         position: int,
     *         exercises: array<int, array{
     *             number: string,
     *             title: string,
     *             description: string,
     *             exercise_type: string,
     *             function_name: string,
     *             input_description: string,
     *             output_description: string,
     *             examples: array<int, array{arguments: array<int, mixed>, expected: mixed}>,
     *             considerations: array<int, string>,
     *             starter_code: string,
     *             test_cases: array<int, array{arguments: array<int, mixed>, expected: mixed, is_public: bool, tolerance?: float}>,
     *             difficulty: string,
     *             position: int
     *         }>
     *     }>
     * }>
     */
    private function curriculum(): array
    {
        return [
            [
                'name' => 'Tema 1',
                'slug' => 'tema-1',
                'title' => 'Condicionales',
                'description' => 'Primeros ejercicios para practicar salidas de texto en Python.',
                'position' => 1,
                'topics' => [
                    [
                        'name' => 'Condicionales',
                        'slug' => 'condicionales',
                        'description' => 'Ejercicios introductorios con funciones y decisiones simples.',
                        'position' => 1,
                        'exercises' => [
                            $this->exercise('01', 'Hola mundo', $this->holaMundoDescription(), 'hola_mundo', "def hola_mundo(n):\n    return None\n", [
                                ['arguments' => [1], 'expected' => 'Hola mundo.', 'is_public' => true],
                                ['arguments' => [0], 'expected' => '', 'is_public' => false],
                                ['arguments' => [-3], 'expected' => '', 'is_public' => false],
                                ['arguments' => [5], 'expected' => 'Hola mundo.', 'is_public' => false],
                            ], 1, [
                                'output_description' => 'La función debe retornar "Hola mundo." si n es mayor que 0, y una cadena vacía en caso contrario.',
                                'considerations' => [
                                    'Usá una condición para decidir si corresponde saludar.',
                                    'No uses print(); retorná el texto desde la función.',
                                ],
                            ]),
                            $this->exercise('02', 'Velocidad = desplazamiento / tiempo', $this->cinematicaDescription(), 'calcular_cinematica', "def calcular_cinematica(desplazamiento, tiempo, velocidad):\n    return None\n", [
                                ['arguments' => [32, 4, null], 'expected' => 'V=8', 'is_public' => true],
                                ['arguments' => [null, 4, 8], 'expected' => 'D=32', 'is_public' => false],
                                ['arguments' => [32, null, 8], 'expected' => 'T=4', 'is_public' => false],
                            ], 2, [
                                'input_description' => 'La función recibirá desplazamiento, tiempo y velocidad como números. Uno de los tres valores será None y debe calcularse. No uses input() dentro de la función.',
                                'output_description' => 'La función debe retornar el dato faltante con el formato V=valor, D=valor o T=valor.',
                                'considerations' => [
                                    'Usá condiciones para detectar cuál de los tres valores es None.',
                                    'No hace falta leer ni separar textos como D=32 o T=4.',
                                    'No uses print(); retorná el texto desde la función.',
                                ],
                            ]),
                            $this->exercise('03', 'Fin de mes', $this->finDeMesDescription(), 'fin_de_mes', "def fin_de_mes(saldo_inicial, cambio_estimado):\n    return None\n", [
                                ['arguments' => [100, -10], 'expected' => 'SI', 'is_public' => true],
                                ['arguments' => [-10, -100], 'expected' => 'NO', 'is_public' => true],
                                ['arguments' => [-10, 100], 'expected' => 'SI', 'is_public' => true],
                                ['arguments' => [100, -1000], 'expected' => 'NO', 'is_public' => true],
                                ['arguments' => [0, 0], 'expected' => 'SI', 'is_public' => false],
                                ['arguments' => [-10000, 10000], 'expected' => 'SI', 'is_public' => false],
                                ['arguments' => [9999, -10000], 'expected' => 'NO', 'is_public' => false],
                            ], 3, [
                                'input_description' => 'La función recibirá dos enteros: saldo_inicial y cambio_estimado. No uses input() dentro de la función.',
                                'output_description' => 'La función debe retornar "SI" si saldo_inicial + cambio_estimado es mayor o igual que 0, y "NO" en otro caso.',
                                'considerations' => [
                                    '-10000 ≤ saldo_inicial, cambio_estimado ≤ 10000',
                                    'No uses print(); retorná exactamente "SI" o "NO".',
                                ],
                            ]),
                            $this->exercise('04', 'Ventas', $this->ventasDescription(), 'ventas_semana', "def ventas_semana(martes, miercoles, jueves, viernes, sabado, domingo):\n    return None\n", [
                                ['arguments' => [185.50, 250.36, 163.45, 535.20, 950.22, 450.38], 'expected' => 'SABADO JUEVES SI', 'is_public' => true],
                                ['arguments' => [10.0, 20.0, 30.0, 40.0, 50.0, 60.0], 'expected' => 'DOMINGO MARTES SI', 'is_public' => false],
                                ['arguments' => [100.0, 100.0, 50.0, 50.0, 25.0, 25.0], 'expected' => 'EMPATE EMPATE NO', 'is_public' => false],
                                ['arguments' => [30.0, 20.0, 10.0, 40.0, 50.0, 25.0], 'expected' => 'SABADO JUEVES NO', 'is_public' => false],
                            ], 4, [
                                'input_description' => 'La función recibirá seis números: martes, miercoles, jueves, viernes, sabado y domingo. No uses input() dentro de la función.',
                                'output_description' => 'La función debe retornar tres palabras separadas por espacios: día de más ventas, día de menos ventas y "SI" si el domingo supera la media semanal o "NO" en caso contrario.',
                                'considerations' => [
                                    'Si hay empate en el máximo o en el mínimo, usá EMPATE para ese valor.',
                                    'Los días llegan como parámetros separados para que puedas compararlos directamente.',
                                    'No uses print(); retorná el texto desde la función.',
                                ],
                            ]),
                            $this->exercise('05', 'En campos de fútbol', $this->camposDeFutbolDescription(), 'campos_de_futbol', "def campos_de_futbol(superficie, estimacion):\n    return None\n", [
                                ['arguments' => [10, 1], 'expected' => 'NO', 'is_public' => true],
                                ['arguments' => [10000, 2], 'expected' => 'SI', 'is_public' => true],
                                ['arguments' => [10000, 3], 'expected' => 'NO', 'is_public' => true],
                                ['arguments' => [11000, 1], 'expected' => 'NO', 'is_public' => true],
                                ['arguments' => [4050, 1], 'expected' => 'SI', 'is_public' => false],
                                ['arguments' => [10800, 1], 'expected' => 'SI', 'is_public' => false],
                                ['arguments' => [100000, 10], 'expected' => 'SI', 'is_public' => false],
                            ], 5, [
                                'input_description' => 'La función recibirá dos enteros positivos: superficie en metros cuadrados y estimacion en campos de fútbol. No uses input() dentro de la función.',
                                'output_description' => 'La función debe retornar "SI" si existe un campo de fútbol legal que haga correcta la estimación, y "NO" en caso contrario.',
                                'considerations' => [
                                    'Un campo legal mide entre 90 y 120 metros de largo, y entre 45 y 90 metros de ancho.',
                                    'Por lo tanto, cada campo puede tener entre 4050 y 10800 metros cuadrados, inclusive.',
                                    '1 ≤ superficie, estimacion ≤ 100000',
                                    'No uses print(); retorná exactamente "SI" o "NO".',
                                ],
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Tema 2',
                'slug' => 'tema-2',
                'title' => 'Bucles',
                'description' => 'Ejercicios para practicar repeticion, acumuladores y contadores.',
                'position' => 2,
                'topics' => [
                    [
                        'name' => 'Bucles',
                        'slug' => 'bucles',
                        'description' => 'Uso inicial de iteración y acumulación.',
                        'position' => 1,
                        'exercises' => [
                            $this->exercise('01', '¡Me caso!', $this->meCasoDescription(), 'coste_boda', "def coste_boda(gastos):\n    return None\n", [
                                ['arguments' => [[100, 200, 100, 200]], 'expected' => 600, 'is_public' => true],
                                ['arguments' => [[50, 25]], 'expected' => 75, 'is_public' => true],
                                ['arguments' => [[999999999]], 'expected' => 999999999, 'is_public' => false],
                                ['arguments' => [[1, 2, 3, 4, 5]], 'expected' => 15, 'is_public' => false],
                            ], 1, [
                                'input_description' => 'La función recibirá una lista de enteros positivos con los gastos de una boda. No uses input() dentro de la función.',
                                'output_description' => 'La función debe retornar el coste total de la boda, es decir, la suma de todos los gastos.',
                                'considerations' => [
                                    'La lista tendrá como mucho 50.000 gastos.',
                                    'La suma total será menor que 10^9.',
                                    'Usá un bucle y un acumulador para calcular el total.',
                                    'No uses print(); retorná el número total.',
                                ],
                            ]),
                            $this->exercise('02', 'Suma con for', 'Devolve la suma de los numeros enteros desde 1 hasta n usando un bucle.', 'sumar_hasta', "def sumar_hasta(n):\n    return None\n", [
                                ['arguments' => [3], 'expected' => 6, 'is_public' => true],
                                ['arguments' => [1], 'expected' => 1, 'is_public' => false],
                                ['arguments' => [5], 'expected' => 15, 'is_public' => false],
                            ], 2, [
                                'output_description' => 'La función debe retornar la suma 1 + 2 + ... + n. No uses la formula directa; practica un bucle.',
                                'considerations' => [
                                    '1 ≤ n ≤ 1000',
                                    'Usa un acumulador para guardar la suma parcial.',
                                ],
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Tema 3',
                'slug' => 'tema-3',
                'title' => 'Ciclos y funciones',
                'description' => 'Práctica inicial de repetición y funciones reutilizables.',
                'position' => 3,
                'topics' => [
                    [
                        'name' => 'Ciclos y funciones',
                        'slug' => 'ciclos-y-funciones',
                        'description' => 'Uso de for, while, parámetros y retorno de valores.',
                        'position' => 1,
                        'exercises' => [
                            $this->exercise('01', 'Ciclo for', 'Recorré una colección con for para acumular resultados.', 'sumar_lista', "def sumar_lista(numeros):\n    return None\n", [
                                ['arguments' => [[1, 2, 3]], 'expected' => 6, 'is_public' => true],
                                ['arguments' => [[]], 'expected' => 0, 'is_public' => false],
                                ['arguments' => [[10, -2, 5]], 'expected' => 13, 'is_public' => false],
                            ], 1),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function holaMundoDescription(): string
    {
        return <<<'TEXT'
Dicen los viejos que en este país el latín era una asignatura obligatoria por la que todos los estudiantes de bachillerato tenían que pasar.

Dicen los viejos que el primer día de clase de latín cualquiera esperaba que los alumnos salieran sabiendo el "rosa rosae".

Dicen los viejos que esa era la primera declinación.

Quizá, dentro de muchos años, nosotros seamos los viejos que contemos batallitas de cómo se enseñaba informática. Y quizá entonces, digamos que en la primera clase de cualquier curso en el que se explicara un lenguaje de programación, se tenía que salir habiendo escrito "un hola mundo".

Y eso es lo que vamos a hacer. Escribir una función que devuelva "Hola mundo." cuando n sea mayor que 0, y una cadena vacía cuando n sea menor o igual que 0.
TEXT;
    }

    private function cinematicaDescription(): string
    {
        return <<<'TEXT'
Javier es profesor de física, y está comenzando a explicar a sus alumnos la cinemática, la rama de la física que describe el movimiento de los objetos sólidos sin considerar las causas que lo originan, estudiando su trayectoria en función del tiempo. Para ello se basa en conceptos como velocidad y aceleración.

Por ahora les ha explicado que la velocidad se determina como el cociente entre el desplazamiento y el tiempo empleado en recorrerlo, según la ecuación velocidad = desplazamiento / tiempo.

Para que se familiaricen con esta fórmula, les va a proponer una serie de preguntas cortas, donde les proporcionará dos de los datos: desplazamiento, tiempo o velocidad. El dato faltante llegará como None y la función deberá contestar con el tercero.
TEXT;
    }

    private function finDeMesDescription(): string
    {
        return <<<'TEXT'
A mí no me asusta el fin del mundo; me asusta el fin de mes, porque no siempre consigo que mis ingresos lleguen conmigo. Los gastos se acumulan, y no sé qué más hacer para estirar mi triste sueldo.

Creo que el primer paso para mejorar mi situación es hacer una estimación de lo bien o lo mal que me va a ir un mes, en función de los ingresos y los gastos previstos. Sé cuánto dinero tengo en el banco al principio, y sé cuánto va a variar.

Ayudame a saber si llegaré a fin de mes con dinero en el banco.
TEXT;
    }

    private function ventasDescription(): string
    {
        return <<<'TEXT'
Debido a la crisis, el bar de Javier ha notado un descenso de las consumiciones. Además, según dicen en los telediarios, la ley antitabaco le está perjudicando aún más. Como no termina de creerse todo lo que dicen en la televisión, ha decidido hacer un estudio de mercado semanal de su establecimiento.

Para ello, ha estado apuntando la caja diaria que se ha realizado en las últimas semanas. Le gustaría saber qué día de la semana se producen el mayor y el menor número de ventas, y si las ventas del domingo superan a la media semanal. De esta manera podrá establecer estrategias de marketing que le permitan recuperar algo de las ganancias perdidas.

Javier abre su bar todos los días menos los lunes, que utiliza para descansar. Para evitar leer listas o entradas completas, la función recibirá las ventas de martes a domingo como parámetros separados.
TEXT;
    }

    private function camposDeFutbolDescription(): string
    {
        return <<<'TEXT'
Según el Sistema Internacional de Unidades, para medir superficies debe utilizarse el metro cuadrado, que es el área en el interior de un cuadrado cuyos lados miden exactamente un metro.

Pero cuando los periodistas tienen que hablar sobre la superficie quemada en un incendio forestal, el espacio arrasado por unas inundaciones, o la cantidad de cultivo echado a perder por un inoportuno granizo, no suelen llevar en el bolsillo cesio 133 para empezar a medir.

La solución, aceptada por el Comité Internacional de Periodistas, es medir la superficie en campos de fútbol, que es algo mucho más fácil de hacer a ojo. Especialmente porque el tamaño de un campo de fútbol es algo impreciso; el largo admitido puede estar entre 90 y 120 metros, y el ancho entre 45 y 90.

Dada una superficie en metros cuadrados y una estimación en campos de fútbol, debemos decidir si existe un campo de dimensiones legales que haga correcta esa estimación.
TEXT;
    }

    private function meCasoDescription(): string
    {
        return <<<'TEXT'
Cada vez que un familiar o un amigo dice las temidas palabras "¡Me caso!", mientras la cara pone gesto de alegría la cartera echa a temblar. Luego viene la hipocresía de la invitación. ¿Pero cómo que "invitación"? La RAE lo deja bien claro: una acepción de invitar es "Pagar el gasto que haga o haya hecho otra persona, por gentileza hacia ella".

Esta opinión dura hasta que quien se casa eres tú. Y es que esto de las bodas es muy bonito, un día inolvidable y todo lo que quieras. Pero el que imprime las invitaciones, el que hace los obsequios para los invitados y el del banquete quieren su dinero, por mucho que feliciten.

Ayudá a sumar todos los gastos de la boda para saber cuánto costará la celebración.
TEXT;
    }

    /**
     * @param  array<int, array{arguments: array<int, mixed>, expected: mixed, is_public: bool, tolerance?: float}>  $testCases
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function exercise(string $number, string $title, string $description, string $functionName, string $starterCode, array $testCases, int $position, array $extra = []): array
    {
        return array_merge([
            'number' => $number,
            'title' => $title,
            'description' => $description,
            'exercise_type' => 'function',
            'function_name' => $functionName,
            'input_description' => 'La función recibirá los valores como parámetros. No uses input() dentro de la función.',
            'output_description' => 'La función debe retornar el resultado esperado. No uses print() para responder.',
            'examples' => $this->publicExamples($testCases),
            'considerations' => [
                'Escribí solo la función solicitada.',
                'La plataforma ejecutará los casos de prueba automáticamente.',
            ],
            'starter_code' => $starterCode,
            'test_cases' => $testCases,
            'difficulty' => 'basic',
            'position' => $position,
        ], $extra);
    }

    /**
     * @param  array<int, array{arguments: array<int, mixed>, expected: mixed, is_public: bool, tolerance?: float}>  $testCases
     * @return array<int, array{arguments: array<int, mixed>, expected: mixed}>
     */
    private function publicExamples(array $testCases): array
    {
        return array_values(array_map(
            fn (array $testCase): array => [
                'arguments' => $testCase['arguments'],
                'expected' => $testCase['expected'],
            ],
            array_filter($testCases, fn (array $testCase): bool => $testCase['is_public']),
        ));
    }
}
