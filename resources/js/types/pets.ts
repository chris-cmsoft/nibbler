export type Animal = {
    id: number;
    name: string;
    caloriesPerDay: number;
    attentionPoints: number;
    svgPath: string;
    svgUrl: string;
};

export type Pet = {
    id: number;
    name: string;
    birthday: string;
    calorieLevel: number;
    attentionLevel: number;
    animal: Animal;
};
